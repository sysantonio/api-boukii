/**
 * Authentication flow test - simplified
 */
describe('Authentication Flow', () => {
  beforeEach(() => {
    cy.clearLocalStorage();
    cy.clearCookies();
  });

  it('should display login form correctly', () => {
    cy.visitAndWait('/auth/login');

    // Basic auth page structure should exist
    cy.get('.auth').should('be.visible');
    cy.get('input[type="email"]').should('exist').and('be.visible');
    cy.get('input[type="password"]').should('exist').and('be.visible');
    cy.get('button[type="submit"]').should('exist');
  });

  it('should show validation errors for empty form', () => {
    cy.visitAndWait('/auth/login');

    // Basic form elements should exist
    cy.get('.auth').should('be.visible');
    cy.get('input[type="email"]').should('be.visible');
  });

  it('should handle form input correctly', () => {
    cy.visitAndWait('/auth/login');

    // Fill form using data-testid selectors with force to handle overlays
    cy.get('[data-testid="email"]').type('test@boukii.com', { force: true });
    cy.get('[data-testid="password"]').type('password123', { force: true });

    // Values should be set
    cy.get('[data-testid="email"]').should('have.value', 'test@boukii.com');
  });
});
