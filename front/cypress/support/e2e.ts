import './commands';
// Ignorar ruido del navegador
Cypress.on('uncaught:exception', (err) => {
  const m = err?.message || '';
  if (m.includes('ResizeObserver loop') || m.includes('Script error') || m.includes('Failed to fetch')) return false;
  return true;
});
beforeEach(() => {
  // Patch console methods for CI visibility
  if (Cypress.env('CI')) {
    cy.window().then((win) => {
      const originalLog = win.console.log;
      const originalError = win.console.error;
      const originalWarn = win.console.warn;
      
      win.console.log = (...args) => {
        cy.task('log', `[LOG] ${args.join(' ')}`);
        originalLog.apply(win.console, args);
      };
      
      win.console.error = (...args) => {
        cy.task('log', `[ERROR] ${args.join(' ')}`);
        originalError.apply(win.console, args);
      };
      
      win.console.warn = (...args) => {
        cy.task('log', `[WARN] ${args.join(' ')}`);
        originalWarn.apply(win.console, args);
      };
    });
  }

  // Configuration
  cy.intercept('GET','/assets/config/runtime-config.json',{fixture:'config/runtime-config.json'}).as('runtime');
  
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
  
  // Set default theme
  cy.window().then((win) => {
    win.localStorage.setItem('theme', 'light');
    win.document.documentElement.dataset.theme = 'light';
    win.document.body.dataset.theme = 'light';
  });
});
