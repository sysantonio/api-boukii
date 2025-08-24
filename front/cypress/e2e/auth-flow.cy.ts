/**
 * Authentication flow test - simplified
 */
describe('Authentication Flow', () => {
  beforeEach(() => {
    cy.clearLocalStorage();
    cy.clearCookies();
  });

  it('should display login form correctly', () => {
    cy.visit('/login');
    cy.wait('@runtime'); // crítico en CI

    cy.get('[data-testid="auth-layout"]').should('be.visible');
    cy.get('[data-testid="auth-card"]').should('be.visible');
    cy.get('[data-testid="email"]').should('exist').and('be.visible');
    cy.get('[data-testid="password"]').should('exist').and('be.visible');
    cy.get('[data-testid="submit"]').should('exist').and('be.enabled');

    // Evitar asserts frágiles por CSS exacto en headless:
    cy.get('[data-testid="auth-title"]').should('contain.text', 'Inicia sesión');
  });

  it('should show validation errors for empty form', () => {
    cy.visit('/login');
    cy.wait('@runtime');

    // Try to submit empty form - button should be disabled for invalid form
    cy.get('[data-cy=login-button]').should('be.disabled');

    // Fill only email (partial form)
    cy.get('#loginEmail').type('test@example.com');

    // Button should still be disabled if password is empty
    cy.get('[data-cy=login-button]').should('be.disabled');
  });

  it('should handle form input correctly', () => {
    cy.visit('/login');
    cy.wait('@runtime');

    // Fill form
    cy.get('#loginEmail').type('test@boukii.com');
    cy.get('#loginPassword').type('password123');

    // Values should be set
    cy.get('#loginEmail').should('have.value', 'test@boukii.com');
    cy.get('#loginPassword').should('have.value', 'password123');

    // Button should be enabled
    cy.get('[data-cy=login-button]').should('not.be.disabled');
  });
});
