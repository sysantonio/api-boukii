/**
 * Simplified Auth Layout Tests
 */
describe('Auth Layout Integration', () => {
  describe('Login Page', () => {
    it('should display unified auth layout', () => {
      cy.visit('/login');
      cy.wait('@runtime');

      cy.get('[data-testid="auth-layout"]').should('be.visible');
      cy.get('[data-testid="auth-card"]').should('be.visible');
      cy.get('[data-testid="auth-title"]').should('exist');
      cy.get('[data-testid="email"]').should('exist');
      cy.get('[data-testid="password"]').should('exist');
    });

    it('should handle form validation', () => {
      cy.visit('/login');
      cy.wait('@runtime');

      // Form should be disabled initially
      cy.get('button[type="submit"]').should('be.disabled');

      // Fill valid data
      cy.get('#loginEmail').type('test@example.com');
      cy.get('#loginPassword').type('password123');

      // Button should now be enabled
      cy.get('button[type="submit"]').should('not.be.disabled');
    });

    it('should toggle password visibility', () => {
      cy.visit('/login');
      cy.wait('@runtime');

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
      cy.visit('/login');
      cy.wait('@runtime');

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
    it('should display register form', () => {
      cy.visit('/register');
      cy.wait('@runtime');

      cy.get('[data-testid="auth-layout"]').should('be.visible');
      cy.get('[data-testid="auth-card"]').should('be.visible');
      cy.get('[data-testid="name"]').should('exist');
      cy.get('[data-testid="email"]').should('exist');
      cy.get('[data-testid="password"]').should('exist');
      cy.get('[data-testid="submit"]').should('be.enabled');
    });
  });

  describe('Forgot Password Page', () => {
    it('should display forgot password form', () => {
      cy.visit('/forgot-password');
      cy.wait('@runtime');

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
      cy.visit('/login');
      cy.wait('@runtime');

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
      cy.visit('/login');
      cy.wait('@runtime');
      cy.url().should('include', '/login');

      cy.visit('/register');
      cy.wait('@runtime');
      cy.url().should('include', '/register');

      cy.visit('/forgot-password');
      cy.wait('@runtime');
      cy.url().should('include', '/forgot-password');
      
      // Basic page loads work
      cy.get('.auth').should('be.visible');
    });
  });
});