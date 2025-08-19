/// <reference types="cypress" />

/**
 * Custom commands for Boukii V5 E2E testing
 */

// Login command
Cypress.Commands.add('login', (email: string, password: string) => {
  cy.visit('/auth/login')
  cy.get('[data-cy=email-input]').type(email)
  cy.get('[data-cy=password-input]').type(password)
  cy.get('[data-cy=login-button]').click()
})

// Login with valid credentials
Cypress.Commands.add('loginWithValidCredentials', () => {
  cy.login('test@boukii.com', 'password123')
})

// Wait for app to load
Cypress.Commands.add('waitForAppToLoad', () => {
  cy.get('[data-cy=app-loaded]', { timeout: 10000 }).should('exist')
})

// Check if user is on login page
Cypress.Commands.add('shouldBeOnLoginPage', () => {
  cy.url().should('include', '/auth/login')
  cy.get('[data-cy=login-form]').should('be.visible')
})

// Check if user is on school selection page
Cypress.Commands.add('shouldBeOnSchoolSelectionPage', () => {
  cy.url().should('include', '/select-school')
  cy.get('[data-cy=school-selection]').should('be.visible')
})

// Check if user is on dashboard
Cypress.Commands.add('shouldBeOnDashboard', () => {
  cy.url().should('include', '/dashboard')
  cy.get('[data-cy=dashboard]').should('be.visible')
})

// Select first school
Cypress.Commands.add('selectFirstSchool', () => {
  cy.get('[data-cy=school-item]').first().click()
})

// Toggle theme
Cypress.Commands.add('toggleTheme', () => {
  cy.get('[data-cy=theme-toggle]').click()
})

// Check theme
Cypress.Commands.add('shouldHaveTheme', (theme: 'light' | 'dark') => {
  cy.get('html').should('have.attr', 'data-theme', theme)
})

// Mock API interceptors
Cypress.Commands.add('mockLoginSuccess', () => {
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
        schools: [
          {
            id: 1,
            name: 'Test School',
            slug: 'test-school',
            status: 'active',
            created_at: '2025-01-01',
            updated_at: '2025-01-01',
            seasons: [
              {
                id: 1,
                school_id: 1,
                name: 'Test Season',
                slug: 'test-season',
                start_date: '2025-01-01',
                end_date: '2025-12-31',
                status: 'active',
                is_current: true,
                created_at: '2025-01-01',
                updated_at: '2025-01-01'
              }
            ]
          }
        ]
      }
    }
  }).as('loginSuccess')
})

Cypress.Commands.add('mockLoginError', () => {
  cy.intercept('POST', '**/api/v5/auth/login', {
    statusCode: 401,
    body: {
      success: false,
      message: 'Invalid credentials',
      errors: {
        email: ['Invalid email or password']
      }
    }
  }).as('loginError')
})

Cypress.Commands.add('mockMultipleSchools', () => {
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
        schools: [
          {
            id: 1,
            name: 'School One',
            slug: 'school-one',
            status: 'active',
            created_at: '2025-01-01',
            updated_at: '2025-01-01',
            seasons: []
          },
          {
            id: 2,
            name: 'School Two',
            slug: 'school-two',
            status: 'active',
            created_at: '2025-01-01',
            updated_at: '2025-01-01',
            seasons: []
          }
        ]
      }
    }
  }).as('loginMultipleSchools')
})

declare global {
  namespace Cypress {
    interface Chainable {
      login(email: string, password: string): Chainable<void>
      loginWithValidCredentials(): Chainable<void>
      waitForAppToLoad(): Chainable<void>
      shouldBeOnLoginPage(): Chainable<void>
      shouldBeOnSchoolSelectionPage(): Chainable<void>
      shouldBeOnDashboard(): Chainable<void>
      selectFirstSchool(): Chainable<void>
      toggleTheme(): Chainable<void>
      shouldHaveTheme(theme: 'light' | 'dark'): Chainable<void>
      mockLoginSuccess(): Chainable<void>
      mockLoginError(): Chainable<void>
      mockMultipleSchools(): Chainable<void>
    }
  }
}