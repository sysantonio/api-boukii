import './commands';

// Stub de matchMedia con soporte para prefers-reduced-motion
if (!('matchMedia' in window)) {
  // @ts-expect-error cypress env
  window.matchMedia = (q: string) => ({
    matches: q.includes('prefers-reduced-motion'),
    media: q,
    onchange: null,
    addListener: () => {}, removeListener: () => {},
    addEventListener: () => {}, removeEventListener: () => {},
    dispatchEvent: () => false,
  });
}

// Silenciar ruidos de navegador sin ocultar errores reales
Cypress.on('uncaught:exception', (err) => {
  const m = err?.message || '';
  if (
    m.includes('ResizeObserver loop') ||
    m.includes('Script error') ||
    m.includes('Failed to fetch')
  ) return false;
  return true;
});
beforeEach(() => {
  // 1) runtime-config primero de todo (algunas pantallas lo leen antes de bootstrap)
  cy.intercept('GET','/assets/config/runtime-config.json',{ fixture:'config/runtime-config.json' }).as('runtime');

  // 2) inyectar CSS para desactivar animaciones/transiciones (evita flakes de layout en headless)
  cy.document().then((doc) => {
    const style = doc.createElement('style');
    style.innerHTML = `
      *, *::before, *::after { transition: none !important; animation: none !important; }
      html:focus-within { scroll-behavior: auto !important; }
    `;
    doc.head.appendChild(style);
  });

  // 3) tema por defecto coherente con la app
  window.localStorage.setItem('theme', 'light');
  document.body.dataset['theme'] = 'light';

  // Basic auth endpoints
  cy.intercept('GET','**/me',{fixture:'auth/me.json'}).as('me');
  cy.intercept('GET','**/context',{fixture:'auth/context.json'}).as('context');
  cy.intercept('GET','**/feature-flags',{fixture:'auth/feature-flags.json'}).as('flags');

  // Data endpoints
  cy.intercept('GET','**/schools*',{fixture:'schools/list.json'}).as('schools');
  cy.intercept('GET','**/seasons*',{fixture:'seasons/list.json'}).as('seasons');
  cy.intercept('GET','**/clients*',{fixture:'clients/list.json'}).as('clients');
  cy.intercept('GET','**/clients/*',{fixture:'clients/detail.json'}).as('clientDetail');

  // Default success for any other API calls
  cy.intercept({method: /GET|POST|PUT|PATCH|DELETE/, url: '**/api/**'}, (req) => {
    req.reply({ statusCode: 200, body: { success: true, data: {} } });
  }).as('apiFallback');
});
