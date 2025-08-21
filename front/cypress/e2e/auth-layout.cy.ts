describe('Auth Layout Integration', () => {
  beforeEach(() => {
    // Mock API endpoints
    cy.intercept('POST', '/api/v5/auth/login', { fixture: 'auth/login-response.json' });
    cy.intercept('POST', '/api/v5/auth/register', { fixture: 'auth/register-response.json' });
    cy.intercept('POST', '/api/v5/auth/forgot-password', { fixture: 'auth/forgot-password-response.json' });
  });

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
      cy.get('.brand__logo').should('be.visible');
      cy.get('.brand__tag').should('contain.text', 'V5');

      // Check features list
      cy.get('.features').should('be.visible');
      cy.get('.features__item').should('have.length', 3);

      // Check card content
      cy.get('.card').should('be.visible');
      cy.get('.card-title').should('contain.text', 'Iniciar Sesión');
    });

    it('should handle form validation', () => {
      // Try to submit empty form
      cy.get('button[type="submit"]').click();

      // Should show validation errors
      cy.get('[data-cy="email-error"]').should('be.visible');
      cy.get('[data-cy="password-error"]').should('be.visible');

      // Fill invalid email
      cy.get('#loginEmail').type('invalid-email');
      cy.get('#loginPassword').type('12'); // Too short

      cy.get('button[type="submit"]').click();

      // Should show format validation
      cy.get('[data-cy="email-error"]').should('contain.text', 'válido');
      cy.get('[data-cy="password-error"]').should('contain.text', 'requerida');
    });

    it('should toggle password visibility', () => {
      cy.get('#loginPassword').type('mypassword');
      
      // Password should be hidden initially
      cy.get('#loginPassword').should('have.attr', 'type', 'password');
      
      // Click show password button
      cy.get('[aria-label*="Mostrar contraseña"]').click();
      
      // Password should now be visible
      cy.get('#loginPassword').should('have.attr', 'type', 'text');
      
      // Button should update aria attributes
      cy.get('[aria-label*="Ocultar contraseña"]').should('exist');
      cy.get('[aria-pressed="true"]').should('exist');
    });

    it('should submit valid login form', () => {
      cy.get('#loginEmail').type('test@example.com');
      cy.get('#loginPassword').type('password123');
      
      cy.get('button[type="submit"]').click();
      
      // Should show loading state
      cy.get('.loading-spinner').should('be.visible');
      cy.get('button[type="submit"]').should('contain.text', 'Cargando');
      
      // Should redirect on success
      cy.url().should('not.include', '/auth/login');
    });

    it('should have proper accessibility attributes', () => {
      // Check ARIA labels
      cy.get('#loginEmail').should('have.attr', 'aria-describedby');
      cy.get('#loginPassword').should('have.attr', 'aria-describedby');
      
      // Check role attributes
      cy.get('[role="form"]').should('exist');
      cy.get('[role="status"]').should('exist');
      
      // Check focus management
      cy.get('#loginEmail').focus().should('be.focused');
      cy.tab().should('have.attr', 'id', 'loginPassword');
    });
  });

  describe('Register Page', () => {
    beforeEach(() => {
      cy.visit('/auth/register');
    });

    it('should display register form with all fields', () => {
      cy.get('.card-title').should('contain.text', 'Crear cuenta');
      
      // Check all form fields
      cy.get('#registerName').should('be.visible');
      cy.get('#registerEmail').should('be.visible');
      cy.get('#registerPassword').should('be.visible');
      cy.get('#registerConfirmPassword').should('be.visible');
    });

    it('should validate password confirmation', () => {
      cy.get('#registerName').type('John Doe');
      cy.get('#registerEmail').type('john@example.com');
      cy.get('#registerPassword').type('password123');
      cy.get('#registerConfirmPassword').type('different123');
      
      cy.get('button[type="submit"]').click();
      
      // Should show password mismatch error
      cy.get('[data-cy="confirm-password-error"]')
        .should('be.visible')
        .should('contain.text', 'no coinciden');
    });

    it('should handle successful registration', () => {
      cy.get('#registerName').type('Jane Smith');
      cy.get('#registerEmail').type('jane@example.com');
      cy.get('#registerPassword').type('securePassword123');
      cy.get('#registerConfirmPassword').type('securePassword123');
      
      cy.get('button[type="submit"]').click();
      
      // Should redirect to login page
      cy.url().should('include', '/auth/login');
      cy.get('[data-cy="success-message"]').should('be.visible');
    });
  });

  describe('Forgot Password Page', () => {
    beforeEach(() => {
      cy.visit('/auth/forgot-password');
    });

    it('should display forgot password form', () => {
      cy.get('.card-title').should('contain.text', 'Recuperar contraseña');
      cy.get('#forgotPasswordEmail').should('be.visible');
      cy.get('button[type="submit"]').should('contain.text', 'Enviar Enlace');
    });

    it('should show success state after submission', () => {
      cy.get('#forgotPasswordEmail').type('user@example.com');
      cy.get('button[type="submit"]').click();
      
      // Should show loading state first
      cy.get('.loading-spinner').should('be.visible');
      
      // Then success state
      cy.get('.success-state').should('be.visible');
      cy.get('.success-icon').should('be.visible');
      cy.get('.card-title').should('contain.text', 'Enlace Enviado');
      
      // Should show submitted email
      cy.get('.email-sent-message').should('contain.text', 'user@example.com');
    });

    it('should allow sending another reset link', () => {
      // Submit initial request
      cy.get('#forgotPasswordEmail').type('user@example.com');
      cy.get('button[type="submit"]').click();
      
      // Wait for success state
      cy.get('.success-state').should('be.visible');
      
      // Click send another
      cy.get('button').contains('Enviar Otro Enlace').click();
      
      // Should return to form state
      cy.get('#forgotPasswordEmail').should('be.visible');
      cy.get('.success-state').should('not.exist');
    });
  });

  describe('Theme Switching', () => {
    it('should adapt to dark theme', () => {
      cy.visit('/auth/login');
      
      // Set dark theme
      cy.get('html').invoke('attr', 'data-theme', 'dark');
      
      // Check that colors have adapted
      cy.get('.auth').should('have.css', 'background-color').and('not.equal', 'rgb(255, 255, 255)');
      cy.get('.card').should('have.css', 'background-color').and('not.equal', 'rgb(255, 255, 255)');
      
      // Text should be light in dark theme
      cy.get('.card-title').should('have.css', 'color').and('not.equal', 'rgb(0, 0, 0)');
    });

    it('should maintain contrast in both themes', () => {
      cy.visit('/auth/login');
      
      // Test light theme contrast
      cy.get('.btn--primary').should('have.css', 'background-color');
      cy.get('.btn--primary').should('have.css', 'color');
      
      // Switch to dark theme
      cy.get('html').invoke('attr', 'data-theme', 'dark');
      
      // Contrast should still be maintained
      cy.get('.btn--primary').should('have.css', 'background-color');
      cy.get('.btn--primary').should('have.css', 'color');
    });
  });

  describe('Responsive Design', () => {
    it('should stack layout on mobile', () => {
      cy.viewport('iphone-x');
      cy.visit('/auth/login');
      
      // Layout should stack vertically on mobile
      cy.get('.auth').should('have.css', 'grid-template-columns', '1fr');
      
      // Hero should be on top
      cy.get('.auth__hero').should('be.visible');
      
      // Card should adapt width
      cy.get('.card').should('have.css', 'width').then((width) => {
        expect(parseFloat(width)).to.be.lessThan(420);
      });
    });

    it('should be usable on small screens', () => {
      cy.viewport(375, 667);
      cy.visit('/auth/login');
      
      // All interactive elements should be reachable
      cy.get('#loginEmail').should('be.visible');
      cy.get('#loginPassword').should('be.visible');
      cy.get('button[type="submit"]').should('be.visible');
      
      // Touch targets should be large enough
      cy.get('button[type="submit"]').should('have.css', 'height', '40px');
    });
  });

  describe('Navigation Between Pages', () => {
    it('should navigate between auth pages', () => {
      cy.visit('/auth/login');
      
      // Go to register
      cy.get('a').contains('Crear cuenta').click();
      cy.url().should('include', '/auth/register');
      
      // Go back to login
      cy.get('a').contains('Iniciar sesión').click();
      cy.url().should('include', '/auth/login');
      
      // Go to forgot password
      cy.get('a').contains('Olvidaste').click();
      cy.url().should('include', '/auth/forgot-password');
      
      // Return to login
      cy.get('a').contains('Recordaste').click();
      cy.url().should('include', '/auth/login');
    });
  });

  describe('Error Handling', () => {
    it('should handle network errors gracefully', () => {
      cy.intercept('POST', '/api/v5/auth/login', { forceNetworkError: true });
      
      cy.visit('/auth/login');
      cy.get('#loginEmail').type('test@example.com');
      cy.get('#loginPassword').type('password123');
      cy.get('button[type="submit"]').click();
      
      // Should show error message
      cy.get('[role="status"]').should('contain.text', 'Error');
      
      // Form should be re-enabled
      cy.get('button[type="submit"]').should('not.be.disabled');
    });

    it('should handle validation errors from server', () => {
      cy.intercept('POST', '/api/v5/auth/register', {
        statusCode: 422,
        body: {
          success: false,
          message: 'Email already exists',
          errors: {
            email: ['The email has already been taken.']
          }
        }
      });
      
      cy.visit('/auth/register');
      cy.get('#registerName').type('John Doe');
      cy.get('#registerEmail').type('existing@example.com');
      cy.get('#registerPassword').type('password123');
      cy.get('#registerConfirmPassword').type('password123');
      
      cy.get('button[type="submit"]').click();
      
      // Should show server error
      cy.get('[data-cy="error-message"]').should('contain.text', 'already exists');
    });
  });
});