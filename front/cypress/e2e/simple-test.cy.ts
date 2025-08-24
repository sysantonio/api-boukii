/**
 * Simple smoke test to verify basic functionality
 */
describe('Basic Application', () => {
  it('should load the application', () => {
    cy.visit('/');
    cy.get('body').should('exist');
  });

  it('should navigate to login page', () => {
    cy.visit('/auth/login');
    cy.url().should('include', '/auth/login');
    cy.get('form').should('exist');
  });

  it('should show login form elements', () => {
    cy.visit('/auth/login');
    cy.get('#loginEmail').should('be.visible');
    cy.get('#loginPassword').should('be.visible');
    cy.get('button[type="submit"]').should('be.visible');
  });
});