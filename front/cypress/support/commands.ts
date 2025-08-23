declare global {
  namespace Cypress {
    interface Chainable {
      login(): Chainable<void>;
      selectSchoolAndSeason(): Chainable<void>;
      setTheme(theme:'light'|'dark'): Chainable<void>;
    }
  }
}
Cypress.Commands.add('login', () => {
  cy.intercept('POST','/api/v5/auth/login',{
    statusCode:200,
    body:{access_token:'fake',token_type:'Bearer',expires_in:3600,
      user:{id:1,name:'Test User',email:'test@boukii.dev'}}
  }).as('loginApi');
  cy.visit('/login');
  cy.get('[data-testid="email"]').type('test@boukii.dev');
  cy.get('[data-testid="password"]').type('Password_123!');
  cy.get('[data-testid="submit"]').click();
  cy.wait('@loginApi');
});
Cypress.Commands.add('selectSchoolAndSeason', () => {
  cy.visit('/select-school');
  cy.wait(['@schools','@seasons']);
  cy.get('[data-testid="school-card"]').first().click();
  cy.url().should('include','/select-season');
  cy.get('[data-testid="season-card"]').first().click();
});
Cypress.Commands.add('setTheme',(theme:'light'|'dark')=>{
  window.localStorage.setItem('theme',theme);
  document.body.dataset.theme = theme;
});
export {};
