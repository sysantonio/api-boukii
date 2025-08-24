/**
 * Authentication flow test - simplified
 */
describe('Authentication Flow', () => {
  beforeEach(() => {
    cy.clearLocalStorage();
    cy.clearCookies();
  });

  it('should display login form correctly', () => {
    cy.visitAndWait('/login');

    // Basic auth page structure should exist
    cy.get('.auth').should('be.visible');
    cy.get('input[type="email"]').should('exist').and('be.visible');
    cy.get('input[type="password"]').should('exist').and('be.visible');
    cy.get('button[type="submit"]').should('exist');
  });

  it('should show validation errors for empty form', () => {
    cy.visitAndWait('/login');

    // Try to submit empty form - button should be disabled for invalid form
    cy.get('button[type="submit"]').should('be.disabled');

    // Fill only email (partial form)
    cy.get('input[type="email"]').type('test@example.com');

    // Button should still be disabled if password is empty
    cy.get('button[type="submit"]').should('be.disabled');
  });

  it('should handle form input correctly', () => {
    cy.visitAndWait('/login');

    // Fill form
    cy.get('input[type="email"]').type('test@boukii.com');
    cy.get('input[type="password"]').type('password123');

    // Values should be set
    cy.get('input[type="email"]').should('have.value', 'test@boukii.com');
    cy.get('input[type="password"]').should('have.value', 'password123');

    // Button should be enabled
    cy.get('button[type="submit"]').should('not.be.disabled');
  });
});
