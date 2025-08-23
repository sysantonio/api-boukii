// Ignora errores de navegador que no son de app (ruido CI)
Cypress.on('uncaught:exception', (err) => {
  const msg = err?.message || '';
  if (
    msg.includes('ResizeObserver loop') ||
    msg.includes('Script error') ||
    msg.includes('Failed to fetch') // mientras se mockean llamadas
  ) {
    return false;
  }
  // Para el resto, deja fallar
  return true;
});

// Mocks globales de API que la app pide al cargar
beforeEach(() => {
  cy.intercept('GET', '/api/v5/me', { fixture: 'auth/me.json' }).as('me');
  cy.intercept('GET', '/api/v5/context', { fixture: 'auth/context.json' }).as('context');
  cy.intercept('GET', '/api/v5/feature-flags', { fixture: 'auth/feature-flags.json' }).as('flags');

  cy.intercept('GET', '/api/v5/schools*', { fixture: 'schools/list.json' }).as('schools');
  cy.intercept('GET', '/api/v5/seasons*', { fixture: 'seasons/list.json' }).as('seasons');

  // Fallback genérico para evitar timeouts si aparece otra ruta
  cy.intercept({ method: /GET|POST|PUT|PATCH|DELETE/, url: '/api/**' }, (req) => {
    req.reply({ ok: true });
  }).as('apiFallback');

  // Asegura tema por defecto
  window.localStorage.setItem('theme', 'light');
  document.body.dataset.theme = 'light';
});
