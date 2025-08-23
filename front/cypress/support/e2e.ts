import './commands';
// Ignorar ruido del navegador
Cypress.on('uncaught:exception', (err) => {
  const m = err?.message || '';
  if (m.includes('ResizeObserver loop') || m.includes('Script error') || m.includes('Failed to fetch')) return false;
  return true;
});
beforeEach(() => {
  cy.intercept('GET','/assets/config/runtime-config.json',{fixture:'config/runtime-config.json'}).as('runtime');
  cy.intercept('GET','/api/v5/me',{fixture:'auth/me.json'}).as('me');
  cy.intercept('GET','/api/v5/context',{fixture:'auth/context.json'}).as('context');
  cy.intercept('GET','/api/v5/feature-flags',{fixture:'auth/feature-flags.json'}).as('flags');
  cy.intercept('GET','/api/v5/schools*',{fixture:'schools/list.json'}).as('schools');
  cy.intercept('GET','/api/v5/seasons*',{fixture:'seasons/list.json'}).as('seasons');
  cy.intercept('GET','/api/v5/clients*',{fixture:'clients/list.json'}).as('clients');
  cy.intercept('GET','/api/v5/clients/*',{fixture:'clients/detail.json'}).as('clientDetail');
  cy.intercept({method:/GET|POST|PUT|PATCH|DELETE/,url:'/api/**'}, (req)=> req.reply({ok:true})).as('apiFallback');
  window.localStorage.setItem('theme','light');
  document.body.dataset.theme = 'light';
});
