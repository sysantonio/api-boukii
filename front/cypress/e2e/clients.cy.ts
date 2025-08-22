const setupAuth = (win: Window) => {
  win.localStorage.setItem('boukii_auth_token', 'token')
  win.localStorage.setItem('boukii_user', JSON.stringify({ id: 1, name: 'Test User', email: 'test@boukii.com' }))
  win.localStorage.setItem('boukii_schools', JSON.stringify([{ id: 1, name: 'Test School', slug: 'test-school', seasons: [{ id: 1, school_id: 1, name: 'Test Season', slug: 'test-season', is_current: true }] }]))
  win.localStorage.setItem('boukii_school_id', '1')
  win.localStorage.setItem('boukii_season_id', '1')
  win.localStorage.setItem('boukii_permissions', JSON.stringify([]))
}

describe('Clients Page', () => {
  beforeEach(() => {
    cy.clearLocalStorage()
  })

  it('filters clients by name', () => {
    cy.intercept('GET', '**/api/v5/clients*', (req) => {
      const q = req.query.q as string | undefined
      if (q) {
        req.reply({
          statusCode: 200,
          body: {
            data: [
              { id: 1, fullName: 'John Doe', email: 'john@example.com', phone: '123', utilizadores: '', sportsSummary: '', status: '', signupDate: '' }
            ],
            meta: { pagination: { page: 1, limit: 10, total: 1, totalPages: 1 } }
          }
        })
      } else {
        req.reply({
          statusCode: 200,
          body: {
            data: [
              { id: 1, fullName: 'John Doe', email: 'john@example.com', phone: '123', utilizadores: '', sportsSummary: '', status: '', signupDate: '' },
              { id: 2, fullName: 'Jane Smith', email: 'jane@example.com', phone: '456', utilizadores: '', sportsSummary: '', status: '', signupDate: '' }
            ],
            meta: { pagination: { page: 1, limit: 10, total: 2, totalPages: 1 } }
          }
        })
      }
    }).as('getClients')

    cy.visit('/clients', { onBeforeLoad: setupAuth })
    cy.wait('@getClients')

    cy.shouldHaveTheme('light')
    cy.toggleTheme()
    cy.shouldHaveTheme('dark')

    cy.get('input[formControlName="q"]').type('john')
    cy.wait('@getClients').its('request.url').should('include', 'q=john')
    cy.get('tbody tr').should('have.length', 1).first().should('contain', 'John Doe')
  })

  it('navigates between pages', () => {
    cy.intercept('GET', '**/api/v5/clients*', (req) => {
      const page = Number(req.query.page || '1')
      if (page === 1) {
        req.reply({
          statusCode: 200,
          body: {
            data: [
              { id: 1, fullName: 'Page One', email: 'one@example.com', phone: '111', utilizadores: '', sportsSummary: '', status: '', signupDate: '' }
            ],
            meta: { pagination: { page: 1, limit: 10, total: 2, totalPages: 2 } }
          }
        })
      } else {
        req.reply({
          statusCode: 200,
          body: {
            data: [
              { id: 2, fullName: 'Page Two', email: 'two@example.com', phone: '222', utilizadores: '', sportsSummary: '', status: '', signupDate: '' }
            ],
            meta: { pagination: { page: 2, limit: 10, total: 2, totalPages: 2 } }
          }
        })
      }
    }).as('getClients')

    cy.visit('/clients', { onBeforeLoad: setupAuth })
    cy.wait('@getClients')

    cy.toggleTheme()
    cy.shouldHaveTheme('dark')

    cy.contains('button', 'Next').click()
    cy.wait('@getClients').its('request.url').should('include', 'page=2')
    cy.get('tbody tr').first().should('contain', 'Page Two')

    cy.contains('button', 'Previous').click()
    cy.wait('@getClients').its('request.url').should('include', 'page=1')
    cy.get('tbody tr').first().should('contain', 'Page One')
  })
})
