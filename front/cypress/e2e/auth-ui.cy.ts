/**
 * Authentication UI responsive and theming tests
 */

describe('Auth UI', () => {
  context('Responsive layout', () => {
    it('shows two columns on desktop and stacks vertically on mobile', () => {
      // Desktop layout
      cy.viewport(1280, 720);
      cy.visit('/auth/login');

      cy.get('.auth-shell')
        .invoke('css', 'grid-template-columns')
        .then((val) => {
          const columns = val.split(' ').filter(Boolean);
          expect(columns.length).to.eq(2);
        });

      // Mobile layout
      cy.viewport('iphone-6');
      cy.visit('/auth/login');
      cy.get('.auth-shell')
        .invoke('css', 'grid-template-columns')
        .then((val) => {
          const columns = val.split(' ').filter(Boolean);
          expect(columns.length).to.eq(1);
        });
    });
  });

  it('toggles password visibility', () => {
    cy.visit('/auth/login');

    cy.get('input[type="password"]').as('password');
    cy.get('@password').type('secret123');
    cy.get('@password').should('have.attr', 'type', 'password');

    cy.get('.password-toggle').click();
    cy.get('@password').should('have.attr', 'type', 'text');

    cy.get('.password-toggle').click();
    cy.get('@password').should('have.attr', 'type', 'password');
  });

  it('navigates between login, register and forgot password pages', () => {
    cy.visit('/auth/login');

    cy.contains('a', 'Crear cuenta').click();
    cy.url().should('include', '/auth/register');

    cy.contains('a', 'Iniciar sesión').click();
    cy.url().should('include', '/auth/login');

    cy.contains('a', 'Olvidaste').click();
    cy.url().should('include', '/auth/forgot-password');

    cy.contains('a', 'Recordaste').click();
    cy.url().should('include', '/auth/login');
  });

  it('maintains layout dimensions between light and dark themes', () => {
    cy.visit('/auth/login');

    cy.get('.auth-shell__card .card').then(($card) => {
      const light = $card[0].getBoundingClientRect();

      cy.get('html').invoke('attr', 'data-theme', 'dark');

      cy.get('.auth-shell__card .card').then(($dark) => {
        const dark = $dark[0].getBoundingClientRect();
        expect(dark.width).to.be.closeTo(light.width, 1);
        expect(dark.height).to.be.closeTo(light.height, 1);
      });
    });
  });
});
