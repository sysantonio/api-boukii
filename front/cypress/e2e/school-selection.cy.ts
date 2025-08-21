describe('School Selection Flow', () => {
  const mockSchools = [
    {
      id: 1,
      name: 'Escuela de Natación Aqua Sport',
      slug: 'aqua-sport',
      active: true,
      createdAt: '2024-01-15T10:00:00Z',
      updatedAt: '2024-03-01T15:30:00Z'
    },
    {
      id: 2,
      name: 'Centro Deportivo Elite Swimming',
      slug: 'elite-swimming',
      active: true,
      createdAt: '2024-02-10T09:15:00Z',
      updatedAt: '2024-03-05T11:45:00Z'
    },
    {
      id: 3,
      name: 'Academia de Natación Marina',
      active: true,
      createdAt: '2024-01-20T14:20:00Z',
      updatedAt: '2024-02-28T16:10:00Z'
    },
    {
      id: 4,
      name: 'Club Natación Neptuno (Inactivo)',
      slug: 'neptuno-inactive',
      active: false,
      createdAt: '2023-12-01T08:30:00Z',
      updatedAt: '2024-01-15T12:00:00Z'
    }
  ];

  const mockUser = {
    id: 1,
    name: 'Test User',
    email: 'test@example.com',
    permissions: ['school-manager']
  };

  beforeEach(() => {
    // Intercept API calls
    cy.intercept('POST', '/api/v5/auth/login', {
      statusCode: 200,
      body: {
        token: 'mock-jwt-token',
        user: mockUser
      }
    }).as('login');

    cy.intercept('GET', '/api/v5/auth/me', {
      statusCode: 200,
      body: mockUser
    }).as('getMe');

    cy.intercept('GET', '/api/v5/schools/all', {
      statusCode: 200,
      body: mockSchools
    }).as('getAllSchools');

    cy.intercept('GET', '/api/v5/schools*', {
      statusCode: 200,
      body: {
        data: mockSchools,
        meta: {
          total: mockSchools.length,
          page: 1,
          perPage: 20,
          lastPage: 1,
          from: 1,
          to: mockSchools.length
        }
      }
    }).as('getSchools');

    cy.intercept('POST', '/api/v5/context/school', {
      statusCode: 200,
      body: { success: true }
    }).as('setSchoolContext');

    cy.intercept('GET', '/api/v5/schools/1', {
      statusCode: 200,
      body: mockSchools[0]
    }).as('getSchoolById');
  });

  describe('Authentication Flow Integration', () => {
    it('should redirect to login when not authenticated', () => {
      cy.visit('/select-school');
      cy.url().should('include', '/auth/login');
    });

    it('should access select-school page when authenticated', () => {
      // Mock authenticated state
      cy.window().then((window) => {
        window.localStorage.setItem('auth_token', 'mock-jwt-token');
      });

      cy.visit('/select-school');
      cy.wait('@getAllSchools');
      
      cy.url().should('include', '/select-school');
      cy.get('[data-testid="page-title"]').should('contain', 'Seleccionar Escuela');
    });
  });

  describe('School Selection Guard Logic', () => {
    beforeEach(() => {
      cy.window().then((window) => {
        window.localStorage.setItem('auth_token', 'mock-jwt-token');
      });
    });

    it('should redirect to select-season when user has only one school', () => {
      const singleSchool = [mockSchools[0]];
      
      cy.intercept('GET', '/api/v5/schools/all', {
        statusCode: 200,
        body: singleSchool
      }).as('getSingleSchool');

      cy.visit('/select-school');
      cy.wait('@getSingleSchool');
      cy.wait('@setSchoolContext');
      
      cy.url().should('include', '/select-season');
    });

    it('should show selection page when user has multiple schools', () => {
      cy.visit('/select-school');
      cy.wait('@getAllSchools');
      
      cy.url().should('include', '/select-school');
      cy.get('.schools-grid').should('be.visible');
      cy.get('.school-card').should('have.length', mockSchools.length);
    });

    it('should redirect to dashboard when user has no schools', () => {
      cy.intercept('GET', '/api/v5/schools/all', {
        statusCode: 200,
        body: []
      }).as('getNoSchools');

      cy.visit('/select-school');
      cy.wait('@getNoSchools');
      
      cy.url().should('include', '/dashboard');
    });
  });

  describe('School Selection Page UI', () => {
    beforeEach(() => {
      cy.window().then((window) => {
        window.localStorage.setItem('auth_token', 'mock-jwt-token');
      });

      cy.visit('/select-school');
      cy.wait('@getSchools');
    });

    it('should display page elements correctly', () => {
      // Check breadcrumb
      cy.get('.breadcrumb-text').should('contain', 'Cuenta / Seleccionar escuela');
      
      // Check page title and subtitle
      cy.get('.page-title').should('contain', 'Seleccionar Escuela');
      cy.get('.page-subtitle').should('contain', 'Elige la escuela con la que deseas trabajar');
      
      // Check search input
      cy.get('.search-input').should('be.visible');
      cy.get('.search-input').should('have.attr', 'placeholder', 'Buscar escuelas...');
    });

    it('should display school cards with correct information', () => {
      cy.get('.school-card').should('have.length.greaterThan', 0);
      
      // Check first school card
      cy.get('.school-card').first().within(() => {
        cy.get('.school-name').should('contain', mockSchools[0].name);
        if (mockSchools[0].slug) {
          cy.get('.school-slug').should('contain', mockSchools[0].slug);
        }
        cy.get('.status-badge').should('contain', 'Activa');
        cy.get('.select-school-button').should('contain', 'Usar esta escuela');
      });
    });

    it('should show inactive schools as disabled', () => {
      const inactiveSchool = mockSchools.find(school => !school.active);
      if (inactiveSchool) {
        cy.contains('.school-name', inactiveSchool.name)
          .closest('.school-card')
          .within(() => {
            cy.get('.status-badge').should('contain', 'Inactiva');
            cy.get('.select-school-button').should('be.disabled');
          });
      }
    });
  });

  describe('Search Functionality', () => {
    beforeEach(() => {
      cy.window().then((window) => {
        window.localStorage.setItem('auth_token', 'mock-jwt-token');
      });

      cy.visit('/select-school');
      cy.wait('@getSchools');
    });

    it('should perform search with API call', () => {
      const searchTerm = 'Aqua Sport';
      
      // Mock search results
      cy.intercept('GET', '/api/v5/schools*search=Aqua%20Sport*', {
        statusCode: 200,
        body: {
          data: [mockSchools[0]],
          meta: {
            total: 1,
            page: 1,
            perPage: 20,
            lastPage: 1,
            from: 1,
            to: 1
          }
        }
      }).as('searchSchools');

      cy.get('.search-input').type(searchTerm);
      
      // Wait for debounce and API call
      cy.wait('@searchSchools');
      
      // Check filtered results
      cy.get('.school-card').should('have.length', 1);
      cy.get('.school-name').should('contain', 'Aqua Sport');
    });

    it('should show search spinner while searching', () => {
      cy.get('.search-input').type('test');
      cy.get('.search-spinner').should('be.visible');
    });

    it('should show no results message for empty search', () => {
      cy.intercept('GET', '/api/v5/schools*search=nonexistent*', {
        statusCode: 200,
        body: {
          data: [],
          meta: {
            total: 0,
            page: 1,
            perPage: 20,
            lastPage: 1,
            from: 0,
            to: 0
          }
        }
      }).as('emptySearch');

      cy.get('.search-input').type('nonexistent');
      cy.wait('@emptySearch');
      
      cy.get('.empty-state').should('be.visible');
      cy.get('.empty-title').should('contain', 'No se encontraron resultados');
    });
  });

  describe('School Selection Process', () => {
    beforeEach(() => {
      cy.window().then((window) => {
        window.localStorage.setItem('auth_token', 'mock-jwt-token');
      });

      cy.visit('/select-school');
      cy.wait('@getSchools');
    });

    it('should select school and navigate to select-season', () => {
      // Click on first active school
      cy.get('.school-card').first().within(() => {
        cy.get('.select-school-button').click();
      });

      // Wait for context setting
      cy.wait('@setSchoolContext');
      cy.wait('@getSchoolById');

      // Verify navigation
      cy.url().should('include', '/select-season');
    });

    it('should show loading spinner during selection', () => {
      // Delay the context setting response
      cy.intercept('POST', '/api/v5/context/school', {
        statusCode: 200,
        body: { success: true },
        delay: 1000
      }).as('setSchoolContextDelayed');

      cy.get('.school-card').first().within(() => {
        cy.get('.select-school-button').click();
        
        // Check for loading state
        cy.get('.button-spinner').should('be.visible');
        cy.get('.select-school-button').should('have.class', 'selecting');
      });
    });

    it('should prevent multiple simultaneous selections', () => {
      // Delay the first selection
      cy.intercept('POST', '/api/v5/context/school', {
        statusCode: 200,
        body: { success: true },
        delay: 2000
      }).as('setSchoolContextSlow');

      // Click first school
      cy.get('.school-card').first().within(() => {
        cy.get('.select-school-button').click();
      });

      // Try to click second school while first is processing
      cy.get('.school-card').eq(1).within(() => {
        cy.get('.select-school-button').should('be.disabled');
      });
    });

    it('should handle selection error gracefully', () => {
      // Mock selection error
      cy.intercept('POST', '/api/v5/context/school', {
        statusCode: 500,
        body: { error: 'Selection failed' }
      }).as('setSchoolContextError');

      cy.get('.school-card').first().within(() => {
        cy.get('.select-school-button').click();
      });

      cy.wait('@setSchoolContextError');

      // Check error state
      cy.get('.error-state').should('be.visible');
      cy.get('.error-title').should('contain', 'Error al seleccionar la escuela');
    });
  });

  describe('Pagination', () => {
    beforeEach(() => {
      // Mock paginated response
      const paginatedSchools = Array.from({ length: 10 }, (_, i) => ({
        id: i + 1,
        name: `Escuela ${i + 1}`,
        slug: `school-${i + 1}`,
        active: true,
        createdAt: '2024-01-01T00:00:00Z',
        updatedAt: '2024-03-01T00:00:00Z'
      }));

      cy.intercept('GET', '/api/v5/schools*', {
        statusCode: 200,
        body: {
          data: paginatedSchools,
          meta: {
            total: 45,
            page: 1,
            perPage: 10,
            lastPage: 5,
            from: 1,
            to: 10
          }
        }
      }).as('getPaginatedSchools');

      cy.window().then((window) => {
        window.localStorage.setItem('auth_token', 'mock-jwt-token');
      });

      cy.visit('/select-school');
      cy.wait('@getPaginatedSchools');
    });

    it('should display pagination controls', () => {
      cy.get('.pagination').should('be.visible');
      cy.get('.pagination-info').should('contain', 'Mostrando 1 a 10 de 45 resultados');
      cy.get('.page-numbers').should('be.visible');
      cy.get('.pagination-button').should('have.length', 2); // Previous and Next
    });

    it('should navigate to different pages', () => {
      // Mock page 2 response
      cy.intercept('GET', '/api/v5/schools*page=2*', {
        statusCode: 200,
        body: {
          data: [],
          meta: {
            total: 45,
            page: 2,
            perPage: 10,
            lastPage: 5,
            from: 11,
            to: 20
          }
        }
      }).as('getPage2');

      cy.get('.page-button').contains('2').click();
      cy.wait('@getPage2');

      cy.get('.page-button.active').should('contain', '2');
    });
  });

  describe('Error States', () => {
    beforeEach(() => {
      cy.window().then((window) => {
        window.localStorage.setItem('auth_token', 'mock-jwt-token');
      });
    });

    it('should display error state on API failure', () => {
      cy.intercept('GET', '/api/v5/schools*', {
        statusCode: 500,
        body: { error: 'Server error' }
      }).as('getSchoolsError');

      cy.visit('/select-school');
      cy.wait('@getSchoolsError');

      cy.get('.error-state').should('be.visible');
      cy.get('.error-title').should('contain', 'Error al cargar las escuelas');
      cy.get('.retry-button').should('be.visible');
    });

    it('should retry loading after error', () => {
      // First request fails
      cy.intercept('GET', '/api/v5/schools*', {
        statusCode: 500,
        body: { error: 'Server error' }
      }).as('getSchoolsError');

      cy.visit('/select-school');
      cy.wait('@getSchoolsError');

      // Mock successful retry
      cy.intercept('GET', '/api/v5/schools*', {
        statusCode: 200,
        body: {
          data: mockSchools,
          meta: {
            total: mockSchools.length,
            page: 1,
            perPage: 20,
            lastPage: 1,
            from: 1,
            to: mockSchools.length
          }
        }
      }).as('getSchoolsRetry');

      cy.get('.retry-button').click();
      cy.wait('@getSchoolsRetry');

      cy.get('.schools-grid').should('be.visible');
      cy.get('.school-card').should('have.length', mockSchools.length);
    });
  });

  describe('Responsive Design', () => {
    beforeEach(() => {
      cy.window().then((window) => {
        window.localStorage.setItem('auth_token', 'mock-jwt-token');
      });
    });

    it('should adapt to mobile viewport', () => {
      cy.viewport(375, 667); // iPhone SE
      cy.visit('/select-school');
      cy.wait('@getSchools');

      // Check mobile-specific styles
      cy.get('.schools-grid').should('have.css', 'grid-template-columns', 'repeat(1, 1fr)');
      cy.get('.pagination-controls').should('be.visible');
    });

    it('should maintain functionality on mobile', () => {
      cy.viewport(375, 667);
      cy.visit('/select-school');
      cy.wait('@getSchools');

      // Test search on mobile
      cy.get('.search-input').should('be.visible').type('test');
      
      // Test school selection on mobile
      cy.get('.school-card').first().within(() => {
        cy.get('.select-school-button').should('be.visible').click();
      });

      cy.wait('@setSchoolContext');
    });
  });
});