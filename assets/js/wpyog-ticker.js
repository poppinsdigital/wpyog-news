/**
 * WPYog News Ticker — front-end behaviour
 *
 * Three animation styles:
 *   scroll — continuous CSS-driven horizontal scroll; JS sets duration.
 *   fade   — items cross-fade one at a time.
 *   flap   — split-flap Solari board effect (airport departure display).
 *
 * All tickers support: pause-on-hover, prefers-reduced-motion,
 * show_count counter updates.
 *
 * @package WPYog_News
 * @since   1.1.3
 */
( function () {
	'use strict';

	/* ------------------------------------------------------------------ */
	/* Bootstrap                                                            */
	/* ------------------------------------------------------------------ */

	function init() {
		var tickers = document.querySelectorAll( '.wpyog-ticker-wrap' );
		tickers.forEach( function ( wrap ) {
			var animation = wrap.dataset.animation || 'scroll';
			var speed     = parseInt( wrap.dataset.speed, 10 ) || 80;
			var pause     = wrap.dataset.pause !== 'false';
			var direction = wrap.dataset.direction || 'left';

			// 'slide' is a legacy alias for 'flap'.
			if ( animation === 'slide' ) { animation = 'flap'; }

			if ( animation === 'scroll' ) {
				initScroll( wrap, speed, pause, direction );
			} else if ( animation === 'fade' ) {
				initFade( wrap, speed, pause );
			} else if ( animation === 'flap' ) {
				initFlap( wrap, speed, pause );
			}
		} );
	}

	/* ------------------------------------------------------------------ */
	/* SCROLL — continuous horizontal marquee via CSS animation            */
	/* ------------------------------------------------------------------ */

	function initScroll( wrap, speed, pause, direction ) {
		var track = wrap.querySelector( '.wpyog-ticker-track' );
		if ( ! track ) { return; }

		var firstContent = track.querySelector( '.wpyog-ticker-content' );
		if ( ! firstContent ) { return; }

		function applyDuration() {
			var width    = firstContent.offsetWidth;
			var duration = width / speed;
			track.style.animationDuration       = duration + 's';
			track.style.animationTimingFunction  = 'linear';
			track.style.animationIterationCount  = 'infinite';
			track.style.animationName            =
				direction === 'right'
					? 'wpyog-ticker-scroll-rtl'
					: 'wpyog-ticker-scroll-ltr';
		}

		requestAnimationFrame( function () { applyDuration(); } );

		var resizeTimer;
		window.addEventListener( 'resize', function () {
			clearTimeout( resizeTimer );
			resizeTimer = setTimeout( applyDuration, 150 );
		} );

		if ( pause ) {
			wrap.addEventListener( 'mouseenter', function () {
				track.style.animationPlayState = 'paused';
			} );
			wrap.addEventListener( 'mouseleave', function () {
				track.style.animationPlayState = 'running';
			} );
		}

		if ( window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) {
			track.style.animationPlayState = 'paused';
		}
	}

	/* ------------------------------------------------------------------ */
	/* FADE — items cross-fade one at a time                               */
	/* ------------------------------------------------------------------ */

	function initFade( wrap, speed, pause ) {
		var cycleWrap = wrap.querySelector( '.wpyog-ticker-cycle' );
		if ( ! cycleWrap ) { return; }

		var items = cycleWrap.querySelectorAll( '.wpyog-ticker-item' );
		if ( items.length < 2 ) { return; }

		var interval = calcInterval( speed );
		var current  = 0;
		var paused   = false;

		// Stack items absolutely.
		cycleWrap.style.position = 'relative';
		cycleWrap.style.overflow = 'hidden';

		items.forEach( function ( item, i ) {
			item.style.position   = 'absolute';
			item.style.top        = '0';
			item.style.left       = '0';
			item.style.right      = '0';
			item.style.width      = '100%';
			item.style.margin     = '0';
			item.style.opacity    = i === 0 ? '1' : '0';
			item.style.transition = 'opacity 0.6s ease';
			item.style.display    = 'flex';
			item.style.alignItems = 'center';
			item.style.gap        = '0.5rem';
		} );

		setWrapHeight( cycleWrap, items );
		window.addEventListener( 'resize', function () {
			setWrapHeight( cycleWrap, items );
		} );

		function showNext() {
			if ( paused ) { return; }
			var prev = current;
			current  = ( current + 1 ) % items.length;
			items[ prev ].style.opacity    = '0';
			items[ current ].style.opacity = '1';
			updateCounter( wrap, current, items.length );
		}

		var timer = setInterval( showNext, interval );
		attachPause( wrap, pause, function ( p ) { paused = p; } );

		if ( window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) {
			clearInterval( timer );
		}
	}

	/* ------------------------------------------------------------------ */
	/* FLAP — push-up slide (departure-board style)                        */
	/*                                                                     */
	/* Current item slides out upward while next item slides in from below.*/
	/* cycleWrap has overflow:hidden so only one item is visible at once.  */
	/* ------------------------------------------------------------------ */

	function initFlap( wrap, speed, pause ) {
		var cycleWrap = wrap.querySelector( '.wpyog-ticker-cycle' );
		if ( ! cycleWrap ) { return; }

		var items = cycleWrap.querySelectorAll( '.wpyog-ticker-item' );
		if ( items.length < 2 ) { return; }

		var interval      = calcInterval( speed );
		var current       = 0;
		var paused        = false;
		var transitioning = false;
		var DUR           = 300; // ms — slide duration

		// Stack items absolutely; overflow:hidden on wrap clips the slide.
		cycleWrap.style.position = 'relative';
		cycleWrap.style.overflow = 'hidden';

		items.forEach( function ( item, i ) {
			item.style.position   = 'absolute';
			item.style.top        = '0';
			item.style.left       = '0';
			item.style.right      = '0';
			item.style.width      = '100%';
			item.style.display    = 'flex';
			item.style.alignItems = 'center';
			item.style.gap        = '0.5rem';
			// Only first item visible; others translated below.
			item.style.transform  = i === 0 ? 'translateY(0)' : 'translateY(100%)';
			item.style.visibility = i === 0 ? 'visible' : 'hidden';
		} );

		setWrapHeight( cycleWrap, items );
		window.addEventListener( 'resize', function () {
			setWrapHeight( cycleWrap, items );
		} );

		function showNext() {
			if ( paused || transitioning ) { return; }
			transitioning = true;

			var prev     = current;
			var next     = ( current + 1 ) % items.length;
			var prevItem = items[ prev ];
			var nextItem = items[ next ];

			// Snap next item below (no transition) then make it visible.
			nextItem.style.transition = 'none';
			nextItem.style.transform  = 'translateY(100%)';
			nextItem.style.visibility = 'visible';

			// One frame to let the browser register the snap position.
			requestAnimationFrame( function () {
				requestAnimationFrame( function () {
					var easing = 'cubic-bezier(0.4, 0, 0.2, 1)';
					var trans  = 'transform ' + DUR + 'ms ' + easing;

					prevItem.style.transition = trans;
					nextItem.style.transition = trans;

					// Slide: prev goes up, next comes up behind it.
					prevItem.style.transform = 'translateY(-100%)';
					nextItem.style.transform = 'translateY(0)';

					setTimeout( function () {
						current = next;

						// Hide previous and reset its position for reuse.
						prevItem.style.transition = 'none';
						prevItem.style.transform  = 'translateY(100%)';
						prevItem.style.visibility = 'hidden';

						updateCounter( wrap, current, items.length );
						transitioning = false;
					}, DUR + 20 );
				} );
			} );
		}

		var timer = setInterval( showNext, interval );
		attachPause( wrap, pause, function ( p ) { paused = p; } );

		if ( window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) {
			clearInterval( timer );
		}
	}

	/* ------------------------------------------------------------------ */
	/* Helpers                                                              */
	/* ------------------------------------------------------------------ */

	/**
	 * Convert speed (px/s unit) to a cycling interval in ms.
	 *   speed=40  (slow)   ≈ 5 000 ms
	 *   speed=80  (medium) ≈ 3 000 ms
	 *   speed=160 (fast)   ≈ 1 500 ms
	 */
	function calcInterval( speed ) {
		return Math.round( Math.max( 1500, 7200 - speed * 22 ) );
	}

	/**
	 * Set cycleWrap height to the tallest item.
	 * Hidden items are temporarily made visible at z:-1 to measure.
	 */
	function setWrapHeight( cycleWrap, items ) {
		requestAnimationFrame( function () {
			var maxH = 0;
			items.forEach( function ( item ) {
				var wasHidden = item.style.visibility === 'hidden';
				if ( wasHidden ) {
					item.style.visibility = 'visible';
					item.style.zIndex     = '-1';
				}
				var h = item.offsetHeight;
				if ( h > maxH ) { maxH = h; }
				if ( wasHidden ) {
					item.style.visibility = 'hidden';
					item.style.zIndex     = '1';
				}
			} );
			if ( maxH > 0 ) { cycleWrap.style.height = maxH + 'px'; }
		} );
	}

	/** Attach mouseenter / mouseleave pause handlers. */
	function attachPause( wrap, pause, setter ) {
		if ( ! pause ) { return; }
		wrap.addEventListener( 'mouseenter', function () { setter( true ); } );
		wrap.addEventListener( 'mouseleave', function () { setter( false ); } );
	}

	/** Update the counter "current" span (if present). */
	function updateCounter( wrap, currentIndex, total ) {
		var counter = wrap.querySelector( '.wpyog-ticker-count-current' );
		if ( counter ) { counter.textContent = currentIndex + 1; }
	}

	/* ------------------------------------------------------------------ */
	/* Run                                                                  */
	/* ------------------------------------------------------------------ */

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}

} )();
