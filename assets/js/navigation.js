/* Navigation progressive Quotidio : liens GET internes, sans rechargement du document. */
(() => {
  'use strict';
  const content = document.getElementById('content');
  const overlay = document.getElementById('quotidio-loading');
  const bar = document.getElementById('quotidio-loading-bar');
  if (!content || !overlay || !bar) return;

  let controller = null;
  let requestId = 0;
  let progressTimer = null;
  let progress = 0;
  const reducedMotion = matchMedia('(prefers-reduced-motion: reduce)').matches;

  function start() {
    clearInterval(progressTimer);
    progress = 8;
    bar.style.width = progress + '%';
    overlay.hidden = false;
    document.body.classList.add('quotidio-is-loading');
    overlay.setAttribute('aria-hidden', 'false');
    if (!reducedMotion) {
      progressTimer = setInterval(() => {
        progress = Math.min(92, progress + Math.max(1, (92 - progress) * 0.13));
        bar.style.width = progress + '%';
      }, 130);
    }
  }

  function finish() {
    clearInterval(progressTimer);
    bar.style.width = '100%';
    document.body.classList.remove('quotidio-is-loading');
    overlay.setAttribute('aria-hidden', 'true');
    const delay = reducedMotion ? 0 : 220;
    setTimeout(() => {
      if (!document.body.classList.contains('quotidio-is-loading')) {
        overlay.hidden = true;
        bar.style.width = '0%';
      }
    }, delay);
  }

  function eligible(link, event) {
    if (!link || event.defaultPrevented || event.button !== 0 ||
        event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return false;
    if (link.hasAttribute('download') || link.target && link.target !== '_self' ||
        link.closest('[contenteditable]') || link.matches('[data-no-transition], .refresh')) return false;
    const url = new URL(link.href, location.href);
    if (!/^https?:$/.test(url.protocol) || url.origin !== location.origin) return false;
    if (url.pathname === location.pathname && url.search === location.search && url.hash) return false;
    if (/\.(?:pdf|zip|png|jpe?g|gif|webp|svg|csv|txt|json|xml|ics|docx?|xlsx?|mp4|webm)$/i.test(url.pathname)) return false;
    if (/\/(?:logout|download)(?:\/|$)/i.test(url.pathname)) return false;
    return true;
  }

  async function executeScripts(root) {
    for (const old of Array.from(root.querySelectorAll('script'))) {
      const script = document.createElement('script');
      for (const attribute of old.attributes) script.setAttribute(attribute.name, attribute.value);
      script.textContent = old.textContent;
      old.replaceWith(script);
      if (script.src && !script.async && !script.defer) {
        await new Promise(resolve => {
          script.addEventListener('load', resolve, { once: true });
          script.addEventListener('error', resolve, { once: true });
        });
      }
    }
  }

  async function navigate(destination, push = true) {
    const id = ++requestId;
    if (controller) controller.abort();
    controller = new AbortController();
    start();
    try {
      const response = await fetch(destination, {
        signal: controller.signal,
        credentials: 'same-origin',
        headers: { 'Accept': 'text/html' }
      });
      if (!response.ok || !response.headers.get('content-type')?.includes('text/html')) {
        throw new Error('Réponse non HTML ou erreur HTTP');
      }
      const doc = new DOMParser().parseFromString(await response.text(), 'text/html');
      const next = doc.querySelector('main#content');
      if (!next) throw new Error('Contenu de page manquant');
      if (id !== requestId) return;

      // Conserver la navigation et les scripts globaux du document.
      content.replaceChildren(...Array.from(next.childNodes).map(node => document.importNode(node, true)));
      if (doc.title) document.title = doc.title;
      if (push) history.pushState({ quotidio: true }, '', response.url);
      window.scrollTo(0, 0);
      await executeScripts(content);
      // Les composants globaux peuvent réinitialiser leurs contrôles ici.
      document.dispatchEvent(new CustomEvent('quotidio:page-loaded', { detail: { url: response.url } }));
      if (typeof window.initThemeSystem === 'function') window.initThemeSystem();
    } catch (error) {
      if (id !== requestId || error.name === 'AbortError') return;
      console.error('[Quotidio] Navigation :', error);
      // Une navigation native reste le repli sûr (sessions, redirections, pages spéciales).
      location.assign(destination);
      return;
    } finally {
      if (id === requestId) finish();
    }
  }

  document.addEventListener('click', event => {
    const link = event.target.closest('a[href]');
    if (!eligible(link, event)) return;
    event.preventDefault();
    navigate(link.href);
  });

  window.addEventListener('popstate', () => navigate(location.href, false));
  window.addEventListener('pageshow', () => {
    clearInterval(progressTimer);
    document.body.classList.remove('quotidio-is-loading');
    overlay.hidden = true;
    bar.style.width = '0%';
  });
  window.quotidioNavigate = navigate;
})();
