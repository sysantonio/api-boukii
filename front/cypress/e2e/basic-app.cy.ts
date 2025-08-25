/**
 * Basic application functionality tests
 */
describe('Basic Application Tests', () => {
  describe('Navigation', () => {
    it('should load homepage and redirect appropriately', () => {
      cy.visit('/');
      cy.url().should((url) => {
        expect(url).to.satisfy((currentUrl) => {
          return currentUrl.includes('/dashboard') || currentUrl.includes('/auth');
        });
      });
    });

    it('should load auth/login page', () => {
      cy.visit('/auth/login');
      cy.url().should('include', '/auth/login');
      cy.get('body').should('be.visible');
    });

    it('should load auth/register page', () => {
      cy.visit('/auth/register');
      cy.url().should('include', '/auth/register');
      cy.get('body').should('be.visible');
    });

    it('should load auth/forgot-password page', () => {
      cy.visit('/auth/forgot-password');
      cy.url().should('include', '/auth/forgot-password');
      cy.get('body').should('be.visible');
    });
  });

  describe('Login Form', () => {
    beforeEach(() => {
      cy.visit('/auth/login');
    });

    it('should display login form', () => {
      cy.get('form').should('exist');
      cy.get('input[type="email"]').should('exist');
      cy.get('input[type="password"]').should('exist');
      cy.get('button[type="submit"]').should('exist');
    });

    it('should accept user input', () => {
      cy.get('input[type="email"]').type('test@example.com', { force: true });
      cy.get('input[type="password"]').type('password123', { force: true });
      
      cy.get('input[type="email"]').should('have.value', 'test@example.com');
      cy.get('input[type="password"]').should('have.value', 'password123');
    });

    it('should show auth shell structure', () => {
      cy.get('.auth').should('exist');
      cy.get('.card').should('exist');
      cy.get('h1').should('exist');
    });
  });

  describe('Theme System', () => {
    it('should have default theme applied', () => {
      cy.visit('/');
      cy.get('html').should('have.attr', 'data-theme');
    });

    it('should persist theme in localStorage', () => {
      cy.visit('/');
      cy.window().then((win) => {
        expect(win.localStorage.getItem('theme')).to.exist;
      });
    });
  });
});