/**
 * Authentication UI tests - Simplified
 */

describe('Auth UI', () => {
  context('Responsive layout', () => {
    it('displays properly on desktop and mobile', () => {
      // Desktop layout
      cy.viewport(1280, 720);
      cy.visit('/auth/login');
      cy.get('.auth').should('be.visible');
      cy.get('.auth__hero').should('be.visible');
      cy.get('.auth__card').should('be.visible');

      // Mobile layout
      cy.viewport('iphone-6');
      cy.visit('/auth/login');
      cy.get('.auth').should('be.visible');
      cy.get('.auth__card').should('be.visible');
    });
  });

  it('toggles password visibility', () => {
    cy.visit('/auth/login');

    cy.get('#loginPassword').as('password');
    cy.get('@password').type('secret123', { force: true });
    cy.get('@password').should('have.attr', 'type', 'password');

    cy.get('button.password-toggle').click({ force: true });
    cy.get('@password').should('have.attr', 'type', 'text');

    cy.get('button.password-toggle').click({ force: true });
    cy.get('@password').should('have.attr', 'type', 'password');
  });

  it('navigates between auth pages', () => {
    // Test basic navigation works
    cy.visit('/auth/login');
    cy.get('.auth').should('be.visible');
    
    cy.visit('/auth/register');
    cy.get('.auth').should('be.visible');
    
    cy.visit('/auth/forgot-password');
    cy.get('.auth').should('be.visible');
  });

  it('maintains layout between light and dark themes', () => {
    cy.visit('/auth/login');

    // Light theme
    cy.get('.auth').should('be.visible');
    cy.get('.card').should('be.visible');

    // Switch to dark theme
    cy.get('html').invoke('attr', 'data-theme', 'dark');
    
    // Layout should still be visible and functional
    cy.get('.auth').should('be.visible');
    cy.get('.card').should('be.visible');
  });
});
