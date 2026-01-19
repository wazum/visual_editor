import {sortTheNodesThroughTheShadowRootsAndSlots} from "@typo3/visual-editor/Frontend/sort-the-nodes-through-the-shadow-roots-and-slots.mjs";

/**
 * @return {void}
 */
export function initSaveScrollPosition() {
  if (!getPosition()) {
    // no position to restore

    document.addEventListener('scrollend', saveScrollPosition);
    window.addEventListener('resize', saveScrollPosition);
    return;
  }
  if ('scrollRestoration' in history) {
    history.scrollRestoration = 'manual';
  }

  document.addEventListener('readystatechange', () => {
    setTimeout(() => {
      scrollToPosition();


      setTimeout(() => {
        // init save listeners:
        document.addEventListener('scrollend', saveScrollPosition);
        window.addEventListener('resize', saveScrollPosition);
      }, 100);
    }, 50);
  });
}

/**
 * @param id {string}
 * @param innerOffsetY {number}
 * @return {void}
 */
function setPosition(id, innerOffsetY) {
  const item = {
    id: id,
    innerOffsetY: innerOffsetY,
    url: window.location.href,
    time: Date.now().toFixed(),
  };

  sessionStorage.setItem('t3-ve-scroll-position', JSON.stringify(item));
}

/**
 * @return {{id: string, innerOffsetY: number}|null}
 */
function getPosition() {
  const item = JSON.parse(sessionStorage.getItem('t3-ve-scroll-position') || '{}');
  if (!item || !item.id || !item.url || item.url !== window.location.href) {
    return null;
  }
  // only if it was saved in the last 1h:
  if (Date.now() - parseInt(item.time, 10) > 3600 * 1000) {
    return null;
  }
  return {
    id: item.id,
    innerOffsetY: item.innerOffsetY,
  };
}

/**
 * @return {void}
 */
function scrollToPosition() {
  const position = getPosition();
  if (!position) {
    return;
  }

  const element = document.getElementById(position.id);
  if (!element) {
    return;
  }

  const elementRect = element.getBoundingClientRect();
  const scrollToY = elementRect.top + position.innerOffsetY;
  window.scrollTo({
    top: scrollToY,
    behavior: 'auto',
  });
}

/**
 * @return {void}
 */
function saveScrollPosition() {
  const possibleSaveTargets = document.querySelectorAll('ve-content-element[id]:not([id=""])');
  // we want to find the nearest element to the top of the viewport that is still above or at the top:

  // We need to order the elements from bottom to top
  const list = sortTheNodesThroughTheShadowRootsAndSlots([...possibleSaveTargets]);

  // so we iterate in reverse order:
  for (const element of list.toReversed()) {
    const elementRect = element.getBoundingClientRect();
    if (elementRect.top < 0 && elementRect.bottom > 0) {
      const innerOffsetY = Math.min(-elementRect.top, elementRect.height);
      setPosition(element.id, innerOffsetY);
      return;
    }
  }
}
