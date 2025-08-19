/**
 * Error Handling E2E Tests
 * Tests various error scenarios and edge cases throughout the application
 */

describe('Error Handling', () => {
  beforeEach(() => {
    cy.clearLocalStorage()
    cy.clearCookies()
  })

  describe('API Error Scenarios', () => {
    it('should handle 500 server errors gracefully', () => {
      cy.intercept('POST', '**/api/v5/auth/login', {
        statusCode: 500,
        body: {
          success: false,
          message: 'Internal server error',
          type: 'https://tools.ietf.org/html/rfc7231#section-6.6.1',
          title: 'Internal Server Error',
          status: 500
        }
      }).as('serverError')
      
      cy.visit('/auth/login')
      cy.get('[data-cy=email-input]').type('test@boukii.com')
      cy.get('[data-cy=password-input]').type('password123')
      cy.get('[data-cy=login-button]').click()
      
      cy.wait('@serverError')
      
      // Should display user-friendly error message
      cy.get('[data-cy=error-message]').should('contain', 'Server error')
        .or(cy.get('.toast').should('contain', 'Something went wrong'))
      
      // Should stay on login page
      cy.shouldBeOnLoginPage()
    })

    it('should handle 422 validation errors from server', () => {
      cy.intercept('POST', '**/api/v5/auth/login', {
        statusCode: 422,
        body: {
          success: false,
          message: 'Validation failed',
          errors: {
            email: ['The email field is required.'],
            password: ['The password must be at least 8 characters.']
          }
        }
      }).as('validationError')
      
      cy.visit('/auth/login')
      cy.get('[data-cy=email-input]').type('test@boukii.com')
      cy.get('[data-cy=password-input]').type('short')
      cy.get('[data-cy=login-button]').click()
      
      cy.wait('@validationError')
      
      // Should display validation errors from server
      cy.get('[data-cy=server-error]').should('contain', 'password must be at least 8 characters')
    })

    it('should handle timeout errors', () => {
      cy.intercept('POST', '**/api/v5/auth/login', { delay: 15000 }).as('timeoutRequest')
      
      cy.visit('/auth/login')
      cy.get('[data-cy=email-input]').type('test@boukii.com')
      cy.get('[data-cy=password-input]').type('password123')
      cy.get('[data-cy=login-button]').click()
      
      // Should show loading state
      cy.get('[data-cy=login-button]').should('be.disabled')
      cy.get('[data-cy=loading-spinner]').should('be.visible')
      
      // Should eventually timeout and show error
      cy.get('[data-cy=error-message]', { timeout: 15000 })
        .should('contain', 'Request timeout')
    })

    it('should handle malformed API responses', () => {
      cy.intercept('POST', '**/api/v5/auth/login', {
        statusCode: 200,
        body: 'invalid json response'
      }).as('malformedResponse')
      
      cy.visit('/auth/login')
      cy.get('[data-cy=email-input]').type('test@boukii.com')
      cy.get('[data-cy=password-input]').type('password123')
      cy.get('[data-cy=login-button]').click()
      
      cy.wait('@malformedResponse')
      
      // Should handle parsing error gracefully
      cy.get('[data-cy=error-message]').should('contain', 'Invalid response')
        .or(cy.get('.toast').should('contain', 'Something went wrong'))
    })
  })

  describe('School Selection Error Scenarios', () => {
    it('should handle no schools available', () => {
      cy.intercept('POST', '**/api/v5/auth/login', {
        statusCode: 200,
        body: {
          success: true,
          data: {
            user: {
              id: 1,
              name: 'Test User',
              email: 'test@boukii.com',
              created_at: '2025-01-01',
              updated_at: '2025-01-01'
            },
            token: 'mock-jwt-token',
            schools: [] // No schools
          }
        }
      }).as('noSchools')
      
      cy.visit('/auth/login')
      cy.get('[data-cy=email-input]').type('test@boukii.com')
      cy.get('[data-cy=password-input]').type('password123')
      cy.get('[data-cy=login-button]').click()
      
      cy.wait('@noSchools')
      
      // Should show no access page or appropriate message
      cy.get('[data-cy=no-schools-message]').should('contain', 'No schools available')
      cy.get('[data-cy=logout-button]').should('be.visible')
    })

    it('should handle school selection API errors', () => {
      cy.mockMultipleSchools()
      
      cy.intercept('POST', '**/api/v5/schools/*/select', {
        statusCode: 403,
        body: {
          success: false,
          message: 'Access denied to this school'
        }
      }).as('schoolSelectionError')
      
      cy.visit('/auth/login')
      cy.get('[data-cy=email-input]').type('test@boukii.com')
      cy.get('[data-cy=password-input]').type('password123')
      cy.get('[data-cy=login-button]').click()
      
      cy.wait('@loginMultipleSchools')
      cy.shouldBeOnSchoolSelectionPage()
      
      cy.selectFirstSchool()
      cy.wait('@schoolSelectionError')
      
      // Should display error and stay on school selection
      cy.get('[data-cy=error-message]').should('contain', 'Access denied')
      cy.shouldBeOnSchoolSelectionPage()
    })
  })

  describe('Navigation Error Scenarios', () => {
    it('should handle invalid routes gracefully', () => {
      cy.visit('/invalid-route-that-does-not-exist')
      
      // Should redirect to appropriate page (login or 404)
      cy.url().should('match', /(auth\/login|404|dashboard)/)
    })

    it('should handle expired authentication tokens', () => {
      // Set expired token
      localStorage.setItem('boukii_v5_token', 'expired-token')
      localStorage.setItem('boukii_v5_user', JSON.stringify({
        id: 1,
        name: 'Test User',
        email: 'test@boukii.com'
      }))
      
      // Mock 401 response for authenticated requests
      cy.intercept('GET', '**/api/v5/**', {
        statusCode: 401,
        body: {
          success: false,
          message: 'Token expired'
        }
      }).as('expiredToken')
      
      cy.visit('/dashboard')
      cy.wait('@expiredToken')
      
      // Should redirect to login and clear invalid auth data
      cy.shouldBeOnLoginPage()
      cy.window().then((win) => {
        expect(win.localStorage.getItem('boukii_v5_token')).to.be.null
      })
    })
  })

  describe('Form Validation Edge Cases', () => {
    it('should handle extremely long email addresses', () => {
      const longEmail = 'a'.repeat(250) + '@example.com'
      
      cy.visit('/auth/login')
      cy.get('[data-cy=email-input]').type(longEmail)
      cy.get('[data-cy=password-input]').type('password123')
      cy.get('[data-cy=login-button]').click()
      
      // Should handle long emails gracefully (either accept or show appropriate error)
      cy.get('mat-error').should('exist')
        .or(cy.get('[data-cy=login-button]').should('be.disabled'))
    })

    it('should handle special characters in passwords', () => {
      const specialPassword = '!@#$%^&*()_+-=[]{}|;:,.<>?'
      
      cy.visit('/auth/login')
      cy.get('[data-cy=email-input]').type('test@example.com')
      cy.get('[data-cy=password-input]').type(specialPassword)
      
      // Should accept special characters
      cy.get('[data-cy=login-button]').should('not.be.disabled')
    })

    it('should handle copy-paste with whitespace', () => {
      cy.visit('/auth/login')
      
      // Simulate copy-paste with extra whitespace
      cy.get('[data-cy=email-input]').invoke('val', '  test@example.com  ')
      cy.get('[data-cy=password-input]').type('password123')
      
      // Should trim whitespace or handle appropriately
      cy.get('[data-cy=login-button]').click()
      
      // Email should be trimmed for API call
      cy.intercept('POST', '**/api/v5/auth/login').as('loginRequest')
      cy.wait('@loginRequest').then((interception) => {
        expect(interception.request.body.email).to.eq('test@example.com')
      })
    })
  })

  describe('Browser Compatibility Edge Cases', () => {
    it('should handle localStorage unavailable', () => {
      // Mock localStorage being unavailable
      cy.window().then((win) => {
        Object.defineProperty(win, 'localStorage', {
          value: null,
          writable: false
        })
      })
      
      cy.visit('/auth/login')
      
      // App should still function without localStorage
      cy.get('[data-cy=login-form]').should('be.visible')
    })

    it('should handle cookies disabled', () => {
      // Disable cookies (if the app uses them)
      cy.clearCookies()
      
      cy.visit('/auth/login')
      cy.get('[data-cy=login-form]').should('be.visible')
      
      // App should function without cookies
      cy.get('[data-cy=email-input]').should('be.enabled')
    })
  })

  describe('Performance Edge Cases', () => {
    it('should handle slow network conditions', () => {
      // Simulate slow network
      cy.intercept('POST', '**/api/v5/auth/login', {
        delay: 5000,
        statusCode: 200,
        body: {
          success: true,
          data: {
            user: { id: 1, name: 'Test User', email: 'test@boukii.com' },
            token: 'mock-token',
            schools: []
          }
        }
      }).as('slowLogin')
      
      cy.visit('/auth/login')
      cy.get('[data-cy=email-input]').type('test@boukii.com')
      cy.get('[data-cy=password-input]').type('password123')
      cy.get('[data-cy=login-button]').click()
      
      // Should show loading state immediately
      cy.get('[data-cy=loading-spinner]').should('be.visible')
      cy.get('[data-cy=login-button]').should('be.disabled')
      
      cy.wait('@slowLogin')
      
      // Should complete successfully
      cy.get('[data-cy=loading-spinner]').should('not.exist')
    })
  })
})