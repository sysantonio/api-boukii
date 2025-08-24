/**
 * Authentication flow test - simplified
 */
describe('Authentication Flow', () => {
  beforeEach(() => {
    cy.clearLocalStorage();
    cy.clearCookies();
  });

  it('should display login form correctly', () => {
    cy.visit('/auth/login');
    
    // Check basic form elements exist
    cy.get('#loginEmail').should('be.visible');
    cy.get('#loginPassword').should('be.visible');
    cy.get('[data-cy=login-button]').should('be.visible');
    cy.get('.auth').should('be.visible');
    cy.get('.card-title').should('contain.text', 'Accede a Boukii');
  });

  it('should show validation errors for empty form', () => {
    cy.visit('/auth/login');
    
    // Try to submit empty form - button should be disabled for invalid form
    cy.get('[data-cy=login-button]').should('be.disabled');
    
    // Fill only email (partial form)
    cy.get('#loginEmail').type('test@example.com');
    
    // Button should still be disabled if password is empty
    cy.get('[data-cy=login-button]').should('be.disabled');
  });

  it('should handle form input correctly', () => {
    cy.visit('/auth/login');
    
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
