/**
 * Theme Switching E2E Tests
 * Tests theme functionality including light/dark/system themes and persistence
 */

describe('Theme Switching', () => {
  beforeEach(() => {
    cy.clearLocalStorage()
    cy.clearCookies()
  })

  describe('Default Theme Behavior', () => {
    it('should load with light theme by default', () => {
      cy.visit('/')
      cy.shouldHaveTheme('light')
    })

    it('should respect system preference when no theme is stored', () => {
      // Mock system dark mode preference
      cy.window().then((win) => {
        Object.defineProperty(win, 'matchMedia', {
          value: (query: string) => ({
            matches: query === '(prefers-color-scheme: dark)',
            addEventListener: () => {},
            removeEventListener: () => {}
          })
        })
      })
      
      cy.visit('/')
      // Should detect and apply system dark theme
      cy.shouldHaveTheme('dark')
    })

    it('should apply stored theme preference on load', () => {
      // Set dark theme in localStorage
      cy.window().then((win) => {
        win.localStorage.setItem('theme', 'dark')
      })
      
      cy.visit('/')
      cy.shouldHaveTheme('dark')
    })
  })

  describe('Theme Toggle Functionality', () => {
    it('should toggle from light to dark theme', () => {
      cy.visit('/')
      cy.shouldHaveTheme('light')
      
      cy.toggleTheme()
      cy.shouldHaveTheme('dark')
    })

    it('should cycle through all theme options (light → dark → system → light)', () => {
      cy.visit('/')
      cy.shouldHaveTheme('light')
      
      // Light → Dark
      cy.toggleTheme()
      cy.shouldHaveTheme('dark')
      
      // Dark → System (should resolve to light or dark based on system)
      cy.toggleTheme()
      cy.get('html').should('have.attr', 'data-theme').and('match', /^(light|dark)$/)
      
      // System → Light
      cy.toggleTheme()
      cy.shouldHaveTheme('light')
    })

    it('should persist theme selection in localStorage', () => {
      cy.visit('/')
      cy.toggleTheme() // Switch to dark
      
      cy.window().then((win) => {
        expect(win.localStorage.getItem('theme')).to.eq('dark')
      })
      
      // Reload page
      cy.reload()
      
      // Should maintain dark theme
      cy.shouldHaveTheme('dark')
    })
  })

  describe('Theme in Authentication Flow', () => {
    it('should maintain theme during login process', () => {
      cy.mockLoginSuccess()
      
      cy.visit('/auth/login')
      cy.toggleTheme() // Switch to dark
      cy.shouldHaveTheme('dark')
      
      // Complete login
      cy.get('[data-cy=email-input]').type('test@boukii.com')
      cy.get('[data-cy=password-input]').type('password123')
      cy.get('[data-cy=login-button]').click()
      
      cy.wait('@loginSuccess')
      cy.shouldBeOnDashboard()
      
      // Theme should be maintained
      cy.shouldHaveTheme('dark')
    })

    it('should maintain theme during school selection', () => {
      cy.mockMultipleSchools()
      
      cy.intercept('POST', '**/api/v5/schools/*/select', {
        statusCode: 200,
        body: {
          success: true,
          data: { school_id: 1, season_id: 1, permissions: ['read'] }
        }
      }).as('selectSchool')
      
      cy.visit('/auth/login')
      cy.toggleTheme() // Switch to dark
      cy.shouldHaveTheme('dark')
      
      cy.get('[data-cy=email-input]').type('test@boukii.com')
      cy.get('[data-cy=password-input]').type('password123')
      cy.get('[data-cy=login-button]').click()
      
      cy.wait('@loginMultipleSchools')
      cy.shouldBeOnSchoolSelectionPage()
      
      // Theme should be maintained during school selection
      cy.shouldHaveTheme('dark')
      
      cy.selectFirstSchool()
      cy.wait('@selectSchool')
      cy.shouldBeOnDashboard()
      
      // Theme should still be maintained
      cy.shouldHaveTheme('dark')
    })
  })

  describe('Theme Visual Verification', () => {
    it('should apply correct CSS variables for light theme', () => {
      cy.visit('/')
      cy.shouldHaveTheme('light')
      
      // Check that light theme CSS variables are applied
      cy.get('html').should('have.css', 'color-scheme', 'light')
      
      // Verify background color is light
      cy.get('body').should(($body) => {
        const bgColor = $body.css('background-color')
        // Light theme should have a light background (high luminance)
        expect(bgColor).to.match(/rgb\(25[0-5], 25[0-5], 25[0-5]\)|rgb\(2[4-5][0-9], 2[4-5][0-9], 2[4-5][0-9]\)|white/)
      })
    })

    it('should apply correct CSS variables for dark theme', () => {
      cy.visit('/')
      cy.toggleTheme() // Switch to dark
      cy.shouldHaveTheme('dark')
      
      // Check that dark theme CSS variables are applied
      cy.get('html').should('have.css', 'color-scheme', 'dark')
      
      // Verify background color is dark
      cy.get('body').should(($body) => {
        const bgColor = $body.css('background-color')
        // Dark theme should have a dark background (low luminance)
        expect(bgColor).to.match(/rgb\([0-9], [0-9], [0-9]\)|rgb\([1-5][0-9], [1-5][0-9], [1-5][0-9]\)|black/)
      })
    })

    it('should update component colors when theme changes', () => {
      cy.visit('/auth/login')
      
      // Check button color in light theme
      cy.get('[data-cy=login-button]').should(($btn) => {
        const lightColor = $btn.css('background-color')
        
        // Switch to dark theme
        cy.toggleTheme()
        
        // Button color should change
        cy.get('[data-cy=login-button]').should(($darkBtn) => {
          const darkColor = $darkBtn.css('background-color')
          expect(darkColor).to.not.equal(lightColor)
        })
      })
    })
  })

  describe('Theme Toggle Component', () => {
    it('should display current theme in toggle button', () => {
      cy.visit('/')
      
      // Should show light theme indicator
      cy.get('[data-cy=theme-toggle]').should('contain', 'Light')
        .or(cy.get('[data-cy=theme-toggle] mat-icon').should('contain', 'light_mode'))
      
      cy.toggleTheme()
      
      // Should show dark theme indicator
      cy.get('[data-cy=theme-toggle]').should('contain', 'Dark')
        .or(cy.get('[data-cy=theme-toggle] mat-icon').should('contain', 'dark_mode'))
    })

    it('should be accessible via keyboard navigation', () => {
      cy.visit('/')
      
      // Focus theme toggle with keyboard
      cy.get('body').tab() // Navigate to first focusable element
      cy.get('[data-cy=theme-toggle]').should('be.focused')
      
      // Activate with Enter key
      cy.get('[data-cy=theme-toggle]').type('{enter}')
      
      // Should toggle theme
      cy.shouldHaveTheme('dark')
    })

    it('should be accessible via Space key', () => {
      cy.visit('/')
      
      cy.get('[data-cy=theme-toggle]').focus()
      cy.get('[data-cy=theme-toggle]').type(' ') // Space key
      
      // Should toggle theme
      cy.shouldHaveTheme('dark')
    })
  })

  describe('System Theme Detection', () => {
    it('should respond to system theme changes', () => {
      // Set theme to 'system'
      cy.window().then((win) => {
        win.localStorage.setItem('theme', 'system')
      })
      
      // Mock system light mode
      cy.window().then((win) => {
        const mockMatchMedia = (query: string) => ({
          matches: false, // Light mode
          addEventListener: cy.stub(),
          removeEventListener: cy.stub()
        })
        Object.defineProperty(win, 'matchMedia', { value: mockMatchMedia })
      })
      
      cy.visit('/')
      cy.shouldHaveTheme('light')
      
      // Simulate system theme change to dark
      cy.window().then((win) => {
        const mockMatchMedia = (query: string) => ({
          matches: query === '(prefers-color-scheme: dark)', // Dark mode
          addEventListener: cy.stub(),
          removeEventListener: cy.stub()
        })
        Object.defineProperty(win, 'matchMedia', { value: mockMatchMedia })
        
        // Trigger theme change event
        win.dispatchEvent(new MediaQueryListEvent('change', {
          matches: true,
          media: '(prefers-color-scheme: dark)'
        }))
      })
      
      cy.shouldHaveTheme('dark')
    })
  })

  describe('Theme in Storybook', () => {
    it('should apply theme correctly in component documentation', () => {
      // Visit Storybook if available
      cy.visit('http://localhost:6006', { failOnStatusCode: false })
      
      cy.get('body').then($body => {
        if ($body.find('[data-cy=storybook-root]').length > 0) {
          // Storybook is available, test theme switching there
          cy.get('[data-cy=theme-toolbar]').click()
          cy.get('[data-cy=dark-theme-option]').click()
          
          // Verify theme is applied in Storybook
          cy.get('[data-cy=story-canvas]').should('have.attr', 'data-theme', 'dark')
        }
      })
    })
  })

  describe('Theme Performance', () => {
    it('should not cause layout shift when switching themes', () => {
      cy.visit('/auth/login')
      
      // Measure initial layout
      cy.get('[data-cy=login-form]').then($form => {
        const initialHeight = $form.height()
        const initialWidth = $form.width()
        
        cy.toggleTheme()
        
        // Layout should remain stable
        cy.get('[data-cy=login-form]').should($newForm => {
          expect($newForm.height()).to.be.closeTo(initialHeight, 10)
          expect($newForm.width()).to.be.closeTo(initialWidth, 10)
        })
      })
    })

    it('should transition smoothly between themes', () => {
      cy.visit('/')
      
      // Check for CSS transition properties
      cy.get('body').should('have.css', 'transition')
        .and('include', 'background-color')
      
      cy.get('*').should($elements => {
        // Most elements should have transition properties for smooth theme switching
        const elementsWithTransition = $elements.filter((_, el) => {
          const transition = window.getComputedStyle(el).transition
          return transition && transition !== 'none'
        })
        
        expect(elementsWithTransition.length).to.be.greaterThan(0)
      })
    })
  })
})