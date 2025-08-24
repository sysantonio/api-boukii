/**
 * Admin: Users & Roles Management E2E Tests
 * Following Boukii V5 CI-stable patterns
 */
describe('Admin: Users & Roles Management', () => {
  beforeEach(() => {
    // Intercepts with deterministic timing (delay: 0) for CI stability
    cy.intercept('GET', '/api/v5/users*', { 
      fixture: 'admin/users/list.json',
      delay: 0 
    }).as('usersList');
    
    cy.intercept('GET', /\/api\/v5\/users\/\d+$/, { 
      fixture: 'admin/users/detail.json',
      delay: 0
    }).as('userDetail');
    
    cy.intercept('GET', '/api/v5/roles', { 
      fixture: 'admin/roles/list.json',
      delay: 0
    }).as('rolesList');
    
    cy.intercept('PUT', /\/api\/v5\/users\/\d+\/roles/, { 
      statusCode: 200, 
      body: { success: true },
      delay: 0
    }).as('saveUserRoles');

    // Authentication simulation with proper context
    cy.login();
    cy.selectSchoolAndSeason();
  });

  describe('Users List Page', () => {
    it('should display users list with proper structure', () => {
      cy.visitAndWait('/admin/users');
      cy.wait('@usersList');
      
      // Verify page structure using robust selectors
      cy.get('[data-testid="users-page"]').should('be.visible');
      cy.get('[data-testid="page-header"]').should('be.visible');
      cy.get('[data-testid="users-table"]').should('be.visible');
      
      // Verify data loaded correctly
      cy.get('[data-testid="user-row-1"]').should('contain', 'Admin Principal');
      cy.get('[data-testid="user-row-2"]').should('contain', 'Manager Operaciones');
      cy.get('[data-testid="user-row-3"]').should('contain', 'Staff Recepción');
      
      // Verify status badges are displayed
      cy.get('[data-testid="user-status-1"]').should('contain', 'Activo');
      cy.get('[data-testid="user-status-3"]').should('contain', 'Inactivo');
    });

    it('should filter users by text search', () => {
      cy.visitAndWait('/admin/users');
      cy.wait('@usersList');
      
      // Test text search filter
      cy.get('[data-testid="filter-text"]').type('Manager');
      
      // Should show filtered results (mocked behavior)
      cy.get('[data-testid="users-table"]').should('contain', 'Manager Operaciones');
    });

    it('should filter users by role', () => {
      cy.visitAndWait('/admin/users');
      cy.wait('@usersList');
      
      // Wait for roles to load for the select options
      cy.wait('@rolesList');
      
      // Test role filter
      cy.get('[data-testid="filter-role"]').select('staff');
      
      // Should apply filter (mocked behavior)
      cy.get('[data-testid="users-table"]').should('be.visible');
    });

    it('should filter users by status', () => {
      cy.visitAndWait('/admin/users');
      cy.wait('@usersList');
      
      // Test status filter
      cy.get('[data-testid="filter-status"]').select('active');
      
      // Should apply filter (mocked behavior)
      cy.get('[data-testid="users-table"]').should('be.visible');
    });

    it('should navigate to user detail when clicking on user row', () => {
      cy.visitAndWait('/admin/users');
      cy.wait('@usersList');
      
      // Click on user row to navigate to detail
      cy.get('[data-testid="user-row-2"]').click();
      
      // Should navigate to user detail page
      cy.url().should('include', '/admin/users/2');
    });

    it('should handle pagination when available', () => {
      cy.visitAndWait('/admin/users');
      cy.wait('@usersList');
      
      // Check if pagination exists (conditional test)
      cy.get('body').then(($body) => {
        if ($body.find('[data-testid="users-pagination"]').length > 0) {
          cy.get('[data-testid="next-page"]').should('be.visible');
          cy.get('[data-testid="prev-page"]').should('be.visible');
        }
      });
    });
  });

  describe('User Detail Page', () => {
    it('should display user information and role assignment interface', () => {
      cy.visitAndWait('/admin/users/2');
      cy.wait(['@userDetail', '@rolesList']);
      
      // Verify page structure
      cy.get('[data-testid="user-detail-page"]').should('be.visible');
      cy.get('[data-testid="user-info-card"]').should('be.visible');
      cy.get('[data-testid="user-roles-section"]').should('be.visible');
      
      // Verify user information is displayed
      cy.get('[data-testid="user-info-card"]').should('contain', 'Manager Operaciones');
      cy.get('[data-testid="user-info-card"]').should('contain', 'manager@boukii.dev');
    });

    it('should show current role assignments correctly', () => {
      cy.visitAndWait('/admin/users/2');
      cy.wait(['@userDetail', '@rolesList']);
      
      // Verify role checkboxes reflect current assignments
      cy.get('[data-testid="role-checkbox-2"]').within(() => {
        cy.get('input[type="checkbox"]').should('be.checked'); // manager role
      });
      
      cy.get('[data-testid="role-checkbox-1"]').within(() => {
        cy.get('input[type="checkbox"]').should('not.be.checked'); // admin role
      });
      
      cy.get('[data-testid="role-checkbox-3"]').within(() => {
        cy.get('input[type="checkbox"]').should('not.be.checked'); // staff role
      });
    });

    it('should update user roles successfully', () => {
      cy.visitAndWait('/admin/users/2');
      cy.wait(['@userDetail', '@rolesList']);
      
      // Add admin role
      cy.get('[data-testid="role-checkbox-1"]').within(() => {
        cy.get('input[type="checkbox"]').check();
      });
      
      // Add staff role  
      cy.get('[data-testid="role-checkbox-3"]').within(() => {
        cy.get('input[type="checkbox"]').check();
      });
      
      // Save changes
      cy.get('[data-testid="save-roles-btn"]').should('not.be.disabled');
      cy.get('[data-testid="save-roles-btn"]').click();
      cy.wait('@saveUserRoles');
      
      // Verify success message
      cy.get('[data-testid="success-message"]').should('be.visible');
      cy.get('[data-testid="success-message"]').should('contain', 'successfully');
    });

    it('should handle role assignment errors gracefully', () => {
      // Mock error response
      cy.intercept('PUT', /\/api\/v5\/users\/\d+\/roles/, { 
        statusCode: 422,
        body: { 
          error: 'Validation failed',
          message: 'Cannot assign admin role to this user' 
        },
        delay: 0
      }).as('saveUserRolesError');
      
      cy.visitAndWait('/admin/users/2');
      cy.wait(['@userDetail', '@rolesList']);
      
      // Try to assign admin role
      cy.get('[data-testid="role-checkbox-1"]').within(() => {
        cy.get('input[type="checkbox"]').check();
      });
      
      cy.get('[data-testid="save-roles-btn"]').click();
      cy.wait('@saveUserRolesError');
      
      // Verify error message is displayed
      cy.get('[data-testid="error-message"]').should('be.visible');
      cy.get('[data-testid="error-message"]').should('contain', 'Cannot assign admin role');
    });

    it('should allow resetting role changes', () => {
      cy.visitAndWait('/admin/users/2');
      cy.wait(['@userDetail', '@rolesList']);
      
      // Make changes
      cy.get('[data-testid="role-checkbox-1"]').within(() => {
        cy.get('input[type="checkbox"]').check();
      });
      
      // Reset changes
      cy.get('button').contains('Restablecer').click();
      
      // Verify roles are reset to original state
      cy.get('[data-testid="role-checkbox-1"]').within(() => {
        cy.get('input[type="checkbox"]').should('not.be.checked');
      });
      
      cy.get('[data-testid="role-checkbox-2"]').within(() => {
        cy.get('input[type="checkbox"]').should('be.checked');
      });
    });

    it('should navigate back to users list', () => {
      cy.visitAndWait('/admin/users/2');
      cy.wait(['@userDetail', '@rolesList']);
      
      // Click breadcrumb to go back
      cy.get('button').contains('Gestión de Usuarios').click();
      
      // Should navigate back to users list
      cy.url().should('include', '/admin/users');
      cy.url().should('not.include', '/admin/users/2');
    });
  });

  describe('Roles List Page', () => {
    it('should display roles list correctly', () => {
      cy.visitAndWait('/admin/roles');
      cy.wait('@rolesList');
      
      // Verify page structure
      cy.get('[data-testid="roles-page"]').should('be.visible');
      cy.get('[data-testid="page-header"]').should('be.visible');
      
      // Verify role cards are displayed
      cy.get('[data-testid="role-card-1"]').should('be.visible');
      cy.get('[data-testid="role-card-2"]').should('be.visible');
      cy.get('[data-testid="role-card-3"]').should('be.visible');
      
      // Verify role information
      cy.get('[data-testid="role-card-1"]').should('contain', 'Acceso completo');
      cy.get('[data-testid="role-card-2"]').should('contain', 'Gestión de operaciones');
      cy.get('[data-testid="role-card-3"]').should('contain', 'Personal operativo');
    });
  });

  describe('Accessibility & UX', () => {
    it('should be keyboard navigable on users list', () => {
      cy.visitAndWait('/admin/users');
      cy.wait('@usersList');
      
      // Test keyboard navigation
      cy.get('[data-testid="filter-text"]').focus();
      cy.focused().should('have.attr', 'data-testid', 'filter-text');
      
      // Tab to next element
      cy.get('[data-testid="filter-text"]').tab();
      cy.focused().should('have.attr', 'data-testid', 'filter-role');
    });

    it('should be keyboard navigable on user detail', () => {
      cy.visitAndWait('/admin/users/2');
      cy.wait(['@userDetail', '@rolesList']);
      
      // Test keyboard navigation in role checkboxes
      cy.get('[data-testid="role-checkbox-1"]').within(() => {
        cy.get('input[type="checkbox"]').focus();
        cy.focused().should('have.attr', 'type', 'checkbox');
      });
    });

    it('should maintain theme consistency', () => {
      cy.visitAndWait('/admin/users');
      cy.wait('@usersList');
      
      // Verify theme is applied
      cy.get('html').should('have.attr', 'data-theme');
      cy.get('[data-testid="users-page"]').should('be.visible');
      
      // Test theme switching
      cy.window().then((win) => {
        const currentTheme = win.document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        win.localStorage.setItem('theme', newTheme);
        win.document.documentElement.setAttribute('data-theme', newTheme);
      });
      
      cy.reload();
      cy.wait('@usersList');
      cy.get('[data-testid="users-page"]').should('be.visible');
    });

    it('should handle loading states gracefully', () => {
      // Intercept with longer delay to test loading state
      cy.intercept('GET', '/api/v5/users*', { 
        fixture: 'admin/users/list.json',
        delay: 1000 
      }).as('usersListSlow');
      
      cy.visitAndWait('/admin/users');
      
      // Should show loading state
      cy.get('[data-testid="users-page"]').should('be.visible');
      
      // Wait for data to load
      cy.wait('@usersListSlow');
      cy.get('[data-testid="users-table"]').should('be.visible');
    });

    it('should handle empty states appropriately', () => {
      // Mock empty response
      cy.intercept('GET', '/api/v5/users*', { 
        body: {
          data: [],
          meta: { total: 0, page: 1, perPage: 20, lastPage: 1 }
        },
        delay: 0
      }).as('usersListEmpty');
      
      cy.visitAndWait('/admin/users');
      cy.wait('@usersListEmpty');
      
      // Should show empty state
      cy.get('[data-testid="users-table"]').should('be.visible');
      cy.get('tbody').should('contain', 'No hay usuarios');
    });
  });

  describe('Error Handling', () => {
    it('should handle API errors on users list', () => {
      // Mock API error
      cy.intercept('GET', '/api/v5/users*', { 
        statusCode: 500,
        body: { error: 'Internal server error' },
        delay: 0
      }).as('usersListError');
      
      cy.visitAndWait('/admin/users');
      cy.wait('@usersListError');
      
      // Should handle error gracefully and still show page structure
      cy.get('[data-testid="users-page"]').should('be.visible');
    });

    it('should handle API errors on user detail', () => {
      // Mock API error for user detail
      cy.intercept('GET', /\/api\/v5\/users\/\d+$/, { 
        statusCode: 404,
        body: { error: 'User not found' },
        delay: 0
      }).as('userDetailError');
      
      cy.visitAndWait('/admin/users/999');
      cy.wait('@userDetailError');
      
      // Should show error state
      cy.get('[data-testid="user-detail-page"]').should('be.visible');
    });
  });
});