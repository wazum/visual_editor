/**
 * Move `node` inside `parent` before `child` and animate
 * its movement + size change from old position to new position.
 *
 * Signature matches native insertBefore(parent, node, child)
 * @param {Node} parent
 * @param {Node} node
 * @param {Node} [child]
 * @returns {Node} The inserted node.
 */
export function flipInsertBefore(parent, node, child) {
  if (!parent || !node) {
    throw new Error('insertBefore(parent, node, child) requires at least parent and node.');
  }

  // Respect prefers-reduced-motion: just move, no animation.
  const prefersReducedMotion =
    typeof window !== 'undefined' &&
    window.matchMedia &&
    window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // If the node isn't in the document yet, we have no "old" position to animate from.
  const wasConnected = node.isConnected;

  if (prefersReducedMotion || !wasConnected) {
    // Just do the DOM move and exit.
    parent.insertBefore(node, child || null);
    return node;
  }

  // 1. FIRST: measure old position and size.
  const firstRect = node.getBoundingClientRect();

  // 2. Move via native insertBefore.
  // If `child` doesn't belong to `parent`, fall back to append.
  if (child && child.parentNode !== parent) {
    parent.insertBefore(node, null);
  } else {
    parent.insertBefore(node, child || null);
  }

  // 3. LAST: measure new position and size.
  const lastRect = node.getBoundingClientRect();

  // If we somehow can't measure (e.g., display:none), just bail.
  if (!lastRect || lastRect.width === 0 || lastRect.height === 0) {
    return node;
  }

  // 4. INVERT: compute deltas.
  const deltaX = firstRect.left - lastRect.left;
  const deltaY = firstRect.top - lastRect.top;

  let scaleX = firstRect.width / lastRect.width;
  let scaleY = firstRect.height / lastRect.height;

  // Guard against NaN / Infinity.
  if (!isFinite(scaleX) || scaleX === 0) scaleX = 1;
  if (!isFinite(scaleY) || scaleY === 0) scaleY = 1;

  // If nothing changed, no animation needed.
  const noTranslate = Math.abs(deltaX) < 0.5 && Math.abs(deltaY) < 0.5;
  const noScale = Math.abs(scaleX - 1) < 0.01 && Math.abs(scaleY - 1) < 0.01;
  if (noTranslate && noScale) {
    return node;
  }

  // Snapshot inline styles so we can restore them later.
  const originalTransition = node.style.transition;
  const originalTransform = node.style.transform;
  const originalTransformOrigin = node.style.transformOrigin;

  // 5. Apply inverted transform so it visually stays at the "old" position/size.
  node.style.transition = 'none';
  node.style.transformOrigin = 'top left';
  node.style.transform = `translate(${deltaX}px, ${deltaY}px) scale(${scaleX}, ${scaleY})`;

  // Force a reflow so the browser applies the above styles immediately.
  node.getBoundingClientRect();

  // Animation settings (tweak if needed).
  const duration = 200; // ms
  const easing = 'ease-out';

  // 6. PLAY: on the next frame, animate transform back to normal.
  requestAnimationFrame(() => {
    // Use a transition on transform to animate back to the final position/size.
    node.style.transition = `transform ${duration}ms ${easing}`;
    node.style.transform = originalTransform || '';

    function cleanup() {
      // Restore original inline styles.
      node.style.transition = originalTransition || '';
      node.style.transformOrigin = originalTransformOrigin || '';
      // If originalTransform was empty string, this is fine; computed styles still apply.
      node.style.transform = originalTransform || '';
    }

    function onTransitionEnd(event) {
      if (event.propertyName !== 'transform') return;
      node.removeEventListener('transitionend', onTransitionEnd);
      cleanup();
    }

    node.addEventListener('transitionend', onTransitionEnd);

    // Fallback in case transitionend never fires (e.g., element removed mid-animation).
    setTimeout(() => {
      node.removeEventListener('transitionend', onTransitionEnd);
      cleanup();
    }, duration + 100);
  });

  return node;
}
