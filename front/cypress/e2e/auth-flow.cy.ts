/**
 * Authentication Flow E2E Tests
 * Tests the complete authentication flow including login, school selection, and navigation
 */

describe('Authentication Flow', () => {
  beforeEach(() => {
    // Clear any existing localStorage and cookies
    cy.clearLocalStorage()
    cy.clearCookies()
  })

  describe('Login Page', () => {
    it('should display login form when visiting root', () => {
      cy.visit('/')
      cy.shouldBeOnLoginPage()
    })

    it('should show validation errors for empty form', () => {
      cy.visit('/auth/login')
      cy.get('[data-cy=login-button]').click()
      
      // Should show validation errors
      cy.get('mat-error').should('contain', 'Email is required')
      cy.get('mat-error').should('contain', 'Password is required')
    })

    it('should show validation error for invalid email format', () => {
      cy.visit('/auth/login')
      cy.get('[data-cy=email-input]').type('invalid-email')
      cy.get('[data-cy=password-input]').type('password123')
      cy.get('[data-cy=login-button]').click()
      
      cy.get('mat-error').should('contain', 'Please enter a valid email address')
    })

    it('should toggle password visibility', () => {
      cy.visit('/auth/login')
      cy.get('[data-cy=password-input]').should('have.attr', 'type', 'password')
      
      cy.get('[data-cy=password-toggle]').click()
      cy.get('[data-cy=password-input]').should('have.attr', 'type', 'text')
      
      cy.get('[data-cy=password-toggle]').click()
      cy.get('[data-cy=password-input]').should('have.attr', 'type', 'password')
    })
  })

  describe('Successful Login Flow', () => {
    it('should complete full login flow with single school (auto-select)', () => {
      // Mock successful login with single school
      cy.mockLoginSuccess()
      
      cy.visit('/auth/login')
      cy.get('[data-cy=email-input]').type('test@boukii.com')
      cy.get('[data-cy=password-input]').type('password123')
      cy.get('[data-cy=login-button]').click()
      
      // Wait for API call
      cy.wait('@loginSuccess')
      
      // Should navigate directly to dashboard (auto-select single school)
      cy.shouldBeOnDashboard()
      
      // Should display user info or dashboard content
      cy.get('[data-cy=dashboard]').should('contain', 'Test User')
    })

    it('should show school selection when user has multiple schools', () => {
      // Mock login with multiple schools
      cy.mockMultipleSchools()
      
      cy.visit('/auth/login')
      cy.get('[data-cy=email-input]').type('test@boukii.com')
      cy.get('[data-cy=password-input]').type('password123')
      cy.get('[data-cy=login-button]').click()
      
      cy.wait('@loginMultipleSchools')
      
      // Should navigate to school selection
      cy.shouldBeOnSchoolSelectionPage()
      
      // Should display available schools
      cy.get('[data-cy=school-item]').should('have.length', 2)
      cy.get('[data-cy=school-item]').first().should('contain', 'School One')
      cy.get('[data-cy=school-item]').last().should('contain', 'School Two')
    })

    it('should navigate to dashboard after selecting school', () => {
      cy.mockMultipleSchools()
      
      // Mock school selection API call
      cy.intercept('POST', '**/api/v5/schools/*/select', {
        statusCode: 200,
        body: {
          success: true,
          data: {
            school_id: 1,
            season_id: 1,
            permissions: ['read', 'write']
          }
        }
      }).as('selectSchool')
      
      cy.visit('/auth/login')
      cy.get('[data-cy=email-input]').type('test@boukii.com')
      cy.get('[data-cy=password-input]').type('password123')
      cy.get('[data-cy=login-button]').click()
      
      cy.wait('@loginMultipleSchools')
      cy.shouldBeOnSchoolSelectionPage()
      
      // Select first school
      cy.selectFirstSchool()
      cy.wait('@selectSchool')
      
      // Should navigate to dashboard
      cy.shouldBeOnDashboard()
    })
  })

  describe('Login Error Handling', () => {
    it('should display error message for invalid credentials', () => {
      cy.mockLoginError()
      
      cy.visit('/auth/login')
      cy.get('[data-cy=email-input]').type('invalid@example.com')
      cy.get('[data-cy=password-input]').type('wrongpassword')
      cy.get('[data-cy=login-button]').click()
      
      cy.wait('@loginError')
      
      // Should stay on login page
      cy.shouldBeOnLoginPage()
      
      // Should display error message (through toast or form error)
      cy.get('[data-cy=error-message]').should('contain', 'Invalid credentials')
        .or(cy.get('.toast').should('contain', 'Login failed'))
    })

    it('should handle network errors gracefully', () => {
      // Mock network error
      cy.intercept('POST', '**/api/v5/auth/login', { forceNetworkError: true }).as('networkError')
      
      cy.visit('/auth/login')
      cy.get('[data-cy=email-input]').type('test@boukii.com')
      cy.get('[data-cy=password-input]').type('password123')
      cy.get('[data-cy=login-button]').click()
      
      cy.wait('@networkError')
      
      // Should display network error message
      cy.get('[data-cy=error-message]').should('contain', 'Network error')
        .or(cy.get('.toast').should('contain', 'Connection error'))
    })
  })

  describe('Navigation and Guards', () => {
    it('should redirect unauthenticated users to login', () => {
      cy.visit('/dashboard')
      cy.shouldBeOnLoginPage()
    })

    it('should redirect authenticated users away from login page', () => {
      // Set up authenticated state
      localStorage.setItem('boukii_v5_token', 'mock-token')
      localStorage.setItem('boukii_v5_user', JSON.stringify({
        id: 1,
        name: 'Test User',
        email: 'test@boukii.com'
      }))
      
      cy.visit('/auth/login')
      
      // Should redirect to dashboard or appropriate page
      cy.url().should('not.include', '/auth/login')
    })

    it('should maintain authentication state across page reloads', () => {
      cy.mockLoginSuccess()
      
      cy.visit('/auth/login')
      cy.get('[data-cy=email-input]').type('test@boukii.com')
      cy.get('[data-cy=password-input]').type('password123')
      cy.get('[data-cy=login-button]').click()
      
      cy.wait('@loginSuccess')
      cy.shouldBeOnDashboard()
      
      // Reload page
      cy.reload()
      
      // Should still be authenticated and on dashboard
      cy.shouldBeOnDashboard()
    })
  })

  describe('Logout Functionality', () => {
    it('should logout user and redirect to login', () => {
      // Set up authenticated state
      cy.mockLoginSuccess()
      cy.visit('/auth/login')
      cy.get('[data-cy=email-input]').type('test@boukii.com')
      cy.get('[data-cy=password-input]').type('password123')
      cy.get('[data-cy=login-button]').click()
      
      cy.wait('@loginSuccess')
      cy.shouldBeOnDashboard()
      
      // Logout
      cy.get('[data-cy=logout-button]').click()
      
      // Should redirect to login
      cy.shouldBeOnLoginPage()
      
      // Should clear authentication data
      cy.window().then((win) => {
        expect(win.localStorage.getItem('boukii_v5_token')).to.be.null
        expect(win.localStorage.getItem('boukii_v5_user')).to.be.null
      })
    })
  })
})