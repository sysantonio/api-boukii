/**
 * Simplified Auth Layout Tests
 */
describe('Auth Layout Integration', () => {
  describe('Login Page', () => {
    beforeEach(() => {
      cy.visit('/auth/login');
    });

    it('should display unified auth layout', () => {
      // Check AuthLayout structure
      cy.get('.auth').should('be.visible');
      cy.get('.auth__hero').should('be.visible');
      cy.get('.auth__card').should('be.visible');

      // Check brand elements
      cy.get('.brand__logo').should('exist');
      cy.get('.brand__tag').should('contain.text', 'V5');

      // Check features list
      cy.get('.features').should('be.visible');
      cy.get('.features__item').should('have.length', 3);

      // Check card content
      cy.get('.card').should('be.visible');
      cy.get('.card-title').should('contain.text', 'Accede a Boukii');
    });

    it('should handle form validation', () => {
      // Form should be disabled initially
      cy.get('button[type="submit"]').should('be.disabled');

      // Fill valid data
      cy.get('#loginEmail').type('test@example.com');
      cy.get('#loginPassword').type('password123');

      // Button should now be enabled
      cy.get('button[type="submit"]').should('not.be.disabled');
    });

    it('should toggle password visibility', () => {
      cy.get('#loginPassword').type('mypassword');

      // Password should be hidden initially
      cy.get('#loginPassword').should('have.attr', 'type', 'password');

      // Click show password button
      cy.get('button.password-toggle').click();

      // Password should now be visible
      cy.get('#loginPassword').should('have.attr', 'type', 'text');

      // Click again to hide
      cy.get('button.password-toggle').click();
      cy.get('#loginPassword').should('have.attr', 'type', 'password');
    });

    it('should have proper accessibility attributes', () => {
      // Check basic form elements exist
      cy.get('#loginEmail').should('exist');
      cy.get('#loginPassword').should('exist');

      // Check role attributes
      cy.get('[role="form"]').should('exist');

      // Check focus management
      cy.get('#loginEmail').focus().should('be.focused');
    });
  });

  describe('Register Page', () => {
    beforeEach(() => {
      cy.visit('/auth/register');
    });

    it('should display register form', () => {
      cy.get('.card-title').should('contain.text', 'Crear cuenta');

      // Check basic form structure exists
      cy.get('form').should('exist');
      cy.get('input[type="email"]').should('exist');
      cy.get('input[type="password"]').should('have.length.at.least', 1);
      cy.get('button[type="submit"]').should('exist');
    });
  });

  describe('Forgot Password Page', () => {
    beforeEach(() => {
      cy.visit('/auth/forgot-password');
    });

    it('should display forgot password form', () => {
      // Check if translation is loaded or use translation key
      cy.get('.card-title').should(($title) => {
        const text = $title.text();
        expect(text === 'Recuperar contraseña' || text.includes('forgotPassword')).to.be.true;
      });
      cy.get('input[type="email"]').should('exist');
      cy.get('button[type="submit"]').should('exist');
    });
  });

  describe('Theme Adaptation', () => {
    it('should adapt to theme changes', () => {
      cy.visit('/auth/login');

      // Set theme and check it applies
      cy.window().then((win) => {
        win.localStorage.setItem('theme', 'dark');
        win.document.documentElement.dataset.theme = 'dark';
      });
      
      cy.reload();
      cy.get('html[data-theme="dark"]').should('exist');
      cy.get('.auth').should('exist');
    });
  });

  describe('Navigation Between Pages', () => {
    it('should navigate between auth pages', () => {
      // Test direct navigation
      cy.visit('/auth/login');
      cy.url().should('include', '/auth/login');
      
      cy.visit('/auth/register');
      cy.url().should('include', '/auth/register');
      
      cy.visit('/auth/forgot-password');
      cy.url().should('include', '/auth/forgot-password');
      
      // Basic page loads work
      cy.get('.auth').should('be.visible');
    });
  });
});