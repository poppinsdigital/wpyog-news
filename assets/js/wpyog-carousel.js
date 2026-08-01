/**
 * WPYog News Carousel — front-end behaviour
 *
 * Vanilla JS, no jQuery dependency (matches wpyog-ticker.js pattern).
 * Slides one item at a time (like a standard news carousel), with:
 *   - configurable slides-to-show (responsive — recalculated on resize)
 *   - autoplay + pause-on-hover
 *   - prev/next arrows, dot navigation
 *   - true infinite loop (clone-buffer technique — wrapping keeps sliding in
 *     the same direction instead of snapping backward) or hard-stop at the ends
 *   - slide or fade transition
 *   - touch swipe support
 *   - prefers-reduced-motion respected
 *
 * @package WPYog_News
 * @since   1.2.0
 */
( function () {
	'use strict';

	function init() {
		var wraps = document.querySelectorAll( '.wpyog-carousel-wrap' );
		wraps.forEach( function ( wrap ) {
			setupCarousel( wrap );
		} );
	}

	function setupCarousel( wrap ) {
		var viewport = wrap.querySelector( '.wpyog-carousel-viewport' );
		var track    = wrap.querySelector( '.wpyog-carousel-track' );
		if ( ! viewport || ! track ) { return; }

		var realItems = Array.prototype.slice.call( track.children );
		if ( ! realItems.length ) { return; }

		var prevBtn = wrap.querySelector( '.wpyog-carousel-prev' );
		var nextBtn = wrap.querySelector( '.wpyog-carousel-next' );
		var dotsWrap = wrap.querySelector( '.wpyog-carousel-dots' );

		var autoplay       = wrap.dataset.autoplay !== 'false';
		var autoplaySpeed  = parseInt( wrap.dataset.autoplaySpeed, 10 ) || 4000;
		var infinite       = wrap.dataset.infinite !== 'false';
		var transition     = wrap.dataset.transition === 'fade' ? 'fade' : 'slide';
		var pauseOnHover   = wrap.dataset.pauseOnHover !== 'false';
		var reducedMotion  = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

		var itemCount = realItems.length;

		// True looping ("last slide slides out to the left, first slide slides
		// in from the right") needs a clone buffer at each end of the track so
		// the transform can keep moving in the same direction. With only one
		// item, or with the fade transition (which already loops seamlessly by
		// swapping opacity rather than position), the buffer isn't needed.
		var loop = infinite && itemCount > 1 && transition !== 'fade';

		var items        = realItems; // becomes real+clones below when loop is on
		var offset        = 0;        // index of the first real item inside `items`
		var currentIndex  = 0;
		var visible       = 1;
		var maxIndex      = 0;
		var timer         = null;
		var isHovering    = false;
		var isDragging    = false;
		var snapListener  = null;
		var snapFallback  = null;

		if ( loop ) {
			offset = itemCount;

			var makeCloneSet = function () {
				return realItems.map( function ( el ) {
					var clone = el.cloneNode( true );
					clone.removeAttribute( 'id' ); // avoid duplicate IDs in the DOM
					clone.setAttribute( 'aria-hidden', 'true' );
					clone.classList.add( 'wpyog-carousel-clone' );
					var focusable = clone.querySelectorAll( 'a[href], button, input, select, textarea, [tabindex]' );
					focusable.forEach( function ( f ) { f.setAttribute( 'tabindex', '-1' ); } );
					return clone;
				} );
			};

			var headFrag = document.createDocumentFragment();
			makeCloneSet().forEach( function ( el ) { headFrag.appendChild( el ); } );
			track.insertBefore( headFrag, track.firstChild );

			var tailFrag = document.createDocumentFragment();
			makeCloneSet().forEach( function ( el ) { tailFrag.appendChild( el ); } );
			track.appendChild( tailFrag );

			items        = Array.prototype.slice.call( track.children );
			currentIndex = offset;
		}

		/* -------------------------------------------------------------- */
		/* Measurement                                                     */
		/* -------------------------------------------------------------- */

		// Distance between two consecutive slides' left edges — measured directly
		// from the DOM rather than parsed from CSS. This makes the math correct
		// regardless of whether spacing comes from flex `gap`, item `margin`,
		// or a mix of both (and stays correct if either is changed later).
		function getStepPx() {
			if ( items.length < 2 ) {
				return items[ 0 ].getBoundingClientRect().width;
			}
			var r0 = items[ 0 ].getBoundingClientRect();
			var r1 = items[ 1 ].getBoundingClientRect();
			return r1.left - r0.left;
		}

		function measure() {
			var step          = getStepPx();
			var viewportWidth = viewport.getBoundingClientRect().width;

			visible = step > 0 ? Math.max( 1, Math.round( viewportWidth / step ) ) : 1;

			if ( loop ) {
				// Any real item can be the leading slide; wrapping is handled by
				// the clone buffer rather than by clamping to items.length - visible.
				maxIndex = itemCount - 1;
			} else {
				maxIndex = Math.max( 0, itemCount - visible );
				if ( currentIndex > maxIndex ) {
					currentIndex = maxIndex;
				}
			}
		}

		/* -------------------------------------------------------------- */
		/* Positioning                                                     */
		/* -------------------------------------------------------------- */

		function applyTransform( index, instant ) {
			var step   = getStepPx();
			var offsetPx = index * step;

			if ( instant ) {
				var prevTransition = track.style.transition;
				track.style.transition = 'none';
				track.style.transform  = 'translateX(-' + offsetPx + 'px)';
				// Force reflow so the "none" transition actually applies before restoring.
				// eslint-disable-next-line no-unused-expressions
				track.offsetHeight;
				track.style.transition = prevTransition;
			} else {
				track.style.transform = 'translateX(-' + offsetPx + 'px)';
			}
		}

		// After a loop-mode slide finishes moving into the clone buffer at
		// either end, jump back — instantly, with no transition — to the
		// equivalent position in the real item set. Because the clone is a
		// pixel-for-pixel duplicate of the real item, this correction is
		// invisible, and the *next* slide continues moving in the same
		// direction instead of reversing.
		function snapIfNeeded() {
			if ( ! loop ) { return; }
			if ( currentIndex >= offset + itemCount ) {
				currentIndex -= itemCount;
				applyTransform( currentIndex, true );
			} else if ( currentIndex < offset ) {
				currentIndex += itemCount;
				applyTransform( currentIndex, true );
			}
		}

		function queueLoopSnap() {
			if ( snapListener ) {
				track.removeEventListener( 'transitionend', snapListener );
			}
			if ( snapFallback ) {
				window.clearTimeout( snapFallback );
			}

			snapListener = function ( e ) {
				if ( e.target !== track || ( e.propertyName && e.propertyName !== 'transform' ) ) { return; }
				track.removeEventListener( 'transitionend', snapListener );
				window.clearTimeout( snapFallback );
				snapListener = null;
				snapFallback = null;
				snapIfNeeded();
			};
			track.addEventListener( 'transitionend', snapListener );

			// Fallback in case transitionend doesn't fire (e.g. hidden tab, 0-width).
			snapFallback = window.setTimeout( function () {
				track.removeEventListener( 'transitionend', snapListener );
				snapListener = null;
				snapFallback = null;
				snapIfNeeded();
			}, 700 );
		}

		function logicalIndex() {
			if ( ! loop ) { return currentIndex; }
			var rel = ( currentIndex - offset ) % itemCount;
			return rel < 0 ? rel + itemCount : rel;
		}

		function goTo( index, opts ) {
			opts = opts || {};

			if ( loop ) {
				// No clamping here — letting the index travel into the clone
				// buffer is what makes the wrap keep sliding in one direction.
			} else if ( infinite ) {
				if ( index < 0 ) { index = maxIndex; }
				if ( index > maxIndex ) { index = 0; }
			} else {
				index = Math.max( 0, Math.min( index, maxIndex ) );
			}

			if ( index === currentIndex && ! opts.force ) { return; }

			if ( transition === 'fade' && ! opts.instant && ! reducedMotion ) {
				track.style.opacity = '0';
				window.setTimeout( function () {
					currentIndex = index;
					applyTransform( currentIndex, true );
					window.requestAnimationFrame( function () {
						track.style.opacity = '1';
					} );
					updateUI();
				}, 260 );
			} else {
				currentIndex = index;
				applyTransform( currentIndex, opts.instant || reducedMotion );
				updateUI();

				if ( loop ) {
					if ( opts.instant || reducedMotion ) {
						snapIfNeeded();
					} else {
						queueLoopSnap();
					}
				}
			}
		}

		function next() { goTo( currentIndex + 1 ); }
		function prev() { goTo( currentIndex - 1 ); }

		/* -------------------------------------------------------------- */
		/* UI: arrows + dots                                               */
		/* -------------------------------------------------------------- */

		function updateArrows() {
			if ( ! prevBtn || ! nextBtn ) { return; }
			if ( infinite ) {
				prevBtn.disabled = false;
				nextBtn.disabled = false;
			} else {
				prevBtn.disabled = ( currentIndex <= 0 );
				nextBtn.disabled = ( currentIndex >= maxIndex );
			}
		}

		function buildDots() {
			if ( ! dotsWrap ) { return; }
			dotsWrap.innerHTML = '';

			for ( var i = 0; i <= maxIndex; i++ ) {
				var dot = document.createElement( 'button' );
				dot.type = 'button';
				dot.className = 'wpyog-carousel-dot';
				dot.setAttribute( 'aria-label', 'Go to slide ' + ( i + 1 ) );
				( function ( idx ) {
					dot.addEventListener( 'click', function () {
						goTo( loop ? offset + idx : idx );
					} );
				} )( i );
				dotsWrap.appendChild( dot );
			}
		}

		function updateDots() {
			if ( ! dotsWrap ) { return; }
			var active = logicalIndex();
			var dots = dotsWrap.querySelectorAll( '.wpyog-carousel-dot' );
			dots.forEach( function ( dot, i ) {
				dot.classList.toggle( 'wpyog-carousel-dot-active', i === active );
			} );
		}

		function updateUI() {
			updateArrows();
			updateDots();
		}

		/* -------------------------------------------------------------- */
		/* Autoplay                                                        */
		/* -------------------------------------------------------------- */

		function startAutoplay() {
			stopAutoplay();
			if ( ! autoplay || reducedMotion || itemCount <= 1 ) { return; }
			timer = window.setInterval( function () {
				if ( isHovering || isDragging ) { return; }
				if ( ! infinite && currentIndex >= maxIndex ) {
					goTo( 0 );
				} else {
					next();
				}
			}, autoplaySpeed );
		}

		function stopAutoplay() {
			if ( timer ) {
				window.clearInterval( timer );
				timer = null;
			}
		}

		/* -------------------------------------------------------------- */
		/* Events                                                          */
		/* -------------------------------------------------------------- */

		if ( prevBtn ) { prevBtn.addEventListener( 'click', function () { prev(); } ); }
		if ( nextBtn ) { nextBtn.addEventListener( 'click', function () { next(); } ); }

		if ( pauseOnHover ) {
			wrap.addEventListener( 'mouseenter', function () { isHovering = true; } );
			wrap.addEventListener( 'mouseleave', function () { isHovering = false; } );
		}

		// Keyboard support when the wrap (or a child) has focus.
		wrap.addEventListener( 'keydown', function ( e ) {
			if ( e.key === 'ArrowLeft' ) { prev(); }
			if ( e.key === 'ArrowRight' ) { next(); }
		} );

		// Touch / pointer swipe.
		var startX = 0;
		var startY = 0;
		var deltaX = 0;

		viewport.addEventListener( 'touchstart', function ( e ) {
			if ( ! e.touches || ! e.touches.length ) { return; }
			isDragging = true;
			startX = e.touches[ 0 ].clientX;
			startY = e.touches[ 0 ].clientY;
			deltaX = 0;
		}, { passive: true } );

		viewport.addEventListener( 'touchmove', function ( e ) {
			if ( ! isDragging || ! e.touches || ! e.touches.length ) { return; }
			deltaX = e.touches[ 0 ].clientX - startX;
		}, { passive: true } );

		viewport.addEventListener( 'touchend', function () {
			if ( ! isDragging ) { return; }
			isDragging = false;
			var THRESHOLD = 40;
			if ( Math.abs( deltaX ) > THRESHOLD ) {
				if ( deltaX < 0 ) { next(); } else { prev(); }
			}
		} );

		// Resize: re-measure and snap to a valid position without animating.
		var resizeTimer;
		window.addEventListener( 'resize', function () {
			window.clearTimeout( resizeTimer );
			resizeTimer = window.setTimeout( function () {
				measure();
				buildDots();
				goTo( currentIndex, { instant: true, force: true } );
			}, 150 );
		} );

		/* -------------------------------------------------------------- */
		/* Boot                                                            */
		/* -------------------------------------------------------------- */

		measure();
		buildDots();
		applyTransform( currentIndex, true );
		updateUI();
		startAutoplay();
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}

}() );
