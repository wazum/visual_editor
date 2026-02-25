/**
 * @param pageId {number}
 * @param languageId {number}
 */
export function pageChanged(pageId, languageId) {
  const newUrl = new URL(window.location.href);
  newUrl.searchParams.set('id', pageId);
  if (languageId) {
    newUrl.searchParams.set('language', languageId);
  } else {
    newUrl.searchParams.delete('language');
  }
  window.history.pushState(null, '', newUrl);

  newUrl.searchParams.set('language', languageId);
  loadModuleDocHeader(newUrl);

  // set href of refresh button to new URL
  document.querySelector('[data-identifier="actions-refresh"]').parentNode.href = newUrl.toString();

  const newUrlTop = new URL(window.top.location.href);
  newUrlTop.searchParams.set('id', pageId);
  if (languageId) {
    newUrlTop.searchParams.set('language', languageId);
  } else {
    newUrlTop.searchParams.delete('language');
  }
  window.top.history.pushState(null, '', newUrlTop);

  ModuleStateStorage.update('web', pageId);
}

let abortController = new AbortController();

/**
 * @param newUrl {URL}
 */
async function loadModuleDocHeader(newUrl) {
  abortController.abort();
  abortController = new AbortController();
  let response;
  try {
    response = await fetch(newUrl, {signal: abortController.signal});
    if (!response.ok) {
      console.error('No doc header found.');
      return;
    }
  } catch (e) {
    if (e.name === 'AbortError') {
      // this is expected when a new request is made before the previous one finished, so we can silently ignore it
      return;
    }
    console.error(e);
    return;
  }

  const html = await response.text();
  const parser = new DOMParser();
  const doc = parser.parseFromString(html, 'text/html');
  const newDocHeaders = doc.querySelectorAll('.module-docheader');
  if (!newDocHeaders.length) {
    console.error('No doc header found. in: ', html);
    return;
  }

  const currentDocHeaderBars = document.querySelectorAll('.module-docheader');
  newDocHeaders.forEach((newBar, index) => {
    const currentBar = currentDocHeaderBars[index];
    if (currentBar) {
      currentBar.replaceWith(newBar);
    }
  });
}
