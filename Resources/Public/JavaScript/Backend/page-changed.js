/**
 * @param pageId {number}
 * @param languageId {number}
 */
export function pageChanged(pageId, languageId) {
  pageId = parseInt(pageId, 10);
  languageId = parseInt(languageId, 10);

  if (isNaN(pageId) || pageId <= 0) {
    console.error('pageChanged: invalid pageId', pageId);
    return;
  }

  if (isNaN(languageId) || languageId < 0) {
    languageId = 0;
  }

  const newUrl = new URL(window.location.href);
  newUrl.searchParams.set('id', pageId);
  if (languageId) {
    newUrl.searchParams.set('languages[0]', languageId);
  } else {
    newUrl.searchParams.delete('languages[0]');
  }
  window.history.pushState(null, '', newUrl);

  newUrl.searchParams.set('languages[0]', languageId);
  loadModuleDocHeader(newUrl);

  // set href of refresh button to new URL
  document.querySelector('[data-identifier="actions-refresh"]').parentNode.href = newUrl.toString();

  const newUrlTop = new URL(window.top.location.href);
  newUrlTop.searchParams.set('id', pageId);
  if (languageId) {
    newUrlTop.searchParams.set('languages[0]', languageId);
  } else {
    newUrlTop.searchParams.delete('languages[0]');
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
