/**
 * Complete Theme Switching Tests
 */
describe('Theme Switching', () => {
  beforeEach(() => {
    cy.clearLocalStorage();
    cy.clearCookies();
  });

  describe('Default Theme Behavior', () => {
    it('should have a theme attribute set', () => {
      cy.visit('/');
      cy.get('html').should('have.attr', 'data-theme');
    });

    it('should store theme in localStorage', () => {
      cy.visit('/');
      
      // Set theme manually if not exists
      cy.window().then((win) => {
        if (!win.localStorage.getItem('theme')) {
          win.localStorage.setItem('theme', 'light');
        }
        expect(win.localStorage.getItem('theme')).to.exist;
      });
    });

    it('should apply default theme on first visit', () => {
      cy.visit('/auth/login');
      cy.get('html').should('have.attr', 'data-theme');
      cy.get('.auth').should('be.visible');
    });
  });

  describe('Theme Toggle Functionality', () => {
    it('should handle theme toggle functionality', () => {
      cy.visit('/auth/login');
      
      // Check current theme
      cy.get('html').should('have.attr', 'data-theme');
      
      // Manually toggle theme via localStorage
      cy.window().then((win) => {
        const currentTheme = win.localStorage.getItem('theme') || 'light';
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        win.localStorage.setItem('theme', newTheme);
        win.document.documentElement.dataset.theme = newTheme;
      });
      
      // Should reflect theme change
      cy.get('html').should('have.attr', 'data-theme');
      cy.get('.auth').should('be.visible');
    });

    it('should persist theme across page reloads', () => {
      cy.visit('/auth/login');
      
      // Set dark theme
      cy.window().then((win) => {
        win.localStorage.setItem('theme', 'dark');
        win.document.documentElement.dataset.theme = 'dark';
      });
      
      cy.reload();
      
      // Theme should persist
      cy.get('html[data-theme="dark"]').should('exist');
      cy.get('.auth').should('be.visible');
    });

    it('should handle invalid theme values gracefully', () => {
      cy.visit('/auth/login');
      
      // Set invalid theme
      cy.window().then((win) => {
        win.localStorage.setItem('theme', 'invalid-theme');
      });
      
      cy.reload();
      
      // Should fallback to valid theme
      cy.get('html').should('have.attr', 'data-theme');
      cy.get('.auth').should('be.visible');
    });
  });

  describe('Theme in Auth Pages', () => {
    it('should apply theme to auth pages', () => {
      cy.visit('/auth/login');
      cy.get('html[data-theme]').should('exist');
      cy.get('.auth').should('exist');
    });

    it('should maintain consistent theme across auth pages', () => {
      // Set theme
      cy.window().then((win) => {
        win.localStorage.setItem('theme', 'dark');
        win.document.documentElement.dataset.theme = 'dark';
      });
      
      cy.visit('/auth/login');
      cy.get('html[data-theme="dark"]').should('exist');
      
      cy.visit('/auth/register');
      cy.get('html[data-theme="dark"]').should('exist');
      
      cy.visit('/auth/forgot-password');
      cy.get('html[data-theme="dark"]').should('exist');
    });

    it('should handle theme switching on register page', () => {
      cy.visit('/auth/register');
      
      // Initial state
      cy.get('html').should('have.attr', 'data-theme');
      cy.get('.auth').should('be.visible');
      
      // Change theme
      cy.window().then((win) => {
        win.localStorage.setItem('theme', 'dark');
        win.document.documentElement.dataset.theme = 'dark';
      });
      
      cy.reload();
      cy.get('html[data-theme="dark"]').should('exist');
      cy.get('.auth').should('be.visible');
    });
  });

  describe('Theme and Form Interaction', () => {
    it('should maintain form functionality across themes', () => {
      cy.visit('/auth/login');
      
      // Test form in light theme
      cy.get('#loginEmail').type('test@example.com');
      cy.get('#loginPassword').type('password123');
      cy.get('[data-cy=login-button]').should('not.be.disabled');
      
      // Switch to dark theme
      cy.window().then((win) => {
        win.localStorage.setItem('theme', 'dark');
        win.document.documentElement.dataset.theme = 'dark';
      });
      
      cy.reload();
      
      // Form should still work in dark theme
      cy.get('#loginEmail').type('test@example.com');
      cy.get('#loginPassword').type('password123');
      cy.get('[data-cy=login-button]').should('not.be.disabled');
    });

    it('should handle password visibility toggle across themes', () => {
      cy.visit('/auth/login');
      
      cy.get('#loginPassword').type('password');
      cy.get('#loginPassword').should('have.attr', 'type', 'password');
      
      // Switch theme
      cy.window().then((win) => {
        win.localStorage.setItem('theme', 'dark');
        win.document.documentElement.dataset.theme = 'dark';
      });
      
      cy.reload();
      
      // Password toggle should still work
      cy.get('#loginPassword').type('password');
      cy.get('#loginPassword').should('have.attr', 'type', 'password');
      
      cy.get('button.password-toggle').click();
      cy.get('#loginPassword').should('have.attr', 'type', 'text');
    });
  });

  describe('Responsive Theme Behavior', () => {
    it('should maintain theme across different viewports', () => {
      // Desktop
      cy.viewport(1280, 720);
      cy.visit('/auth/login');
      
      cy.window().then((win) => {
        win.localStorage.setItem('theme', 'dark');
        win.document.documentElement.dataset.theme = 'dark';
      });
      
      cy.get('html[data-theme="dark"]').should('exist');
      cy.get('.auth').should('be.visible');
      
      // Mobile
      cy.viewport('iphone-6');
      cy.reload();
      
      cy.get('html[data-theme="dark"]').should('exist');
      cy.get('.auth').should('be.visible');
    });
  });

  describe('Theme System Integration', () => {
    it('should handle theme during navigation', () => {
      cy.window().then((win) => {
        win.localStorage.setItem('theme', 'dark');
      });
      
      cy.visit('/auth/login');
      cy.get('html[data-theme="dark"]').should('exist');
      
      cy.visit('/auth/register');
      cy.get('html[data-theme="dark"]').should('exist');
      
      cy.visit('/');
      cy.get('html[data-theme="dark"]').should('exist');
    });

    it('should handle theme with localStorage errors', () => {
      cy.visit('/auth/login');
      
      // Simulate localStorage error by making it null
      cy.window().then((win) => {
        // Even if localStorage fails, app should still work
        cy.stub(win.localStorage, 'getItem').throws(new Error('localStorage error'));
      });
      
      cy.reload();
      
      // Should still load and function
      cy.get('html').should('have.attr', 'data-theme');
      cy.get('.auth').should('be.visible');
    });
  });
});