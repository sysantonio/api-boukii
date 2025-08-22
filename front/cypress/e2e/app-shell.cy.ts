import { AuthV5Service } from '@core/services/auth-v5.service';

const setupAuth = (win: Window) => {
  win.localStorage.setItem('boukii_auth_token', 'token');
  win.localStorage.setItem(
    'boukii_user',
    JSON.stringify({ id: 1, name: 'Test User', email: 'test@boukii.com' })
  );
  win.localStorage.setItem(
    'boukii_schools',
    JSON.stringify([
      {
        id: 1,
        name: 'Test School',
        slug: 'test-school',
        seasons: [
          { id: 1, school_id: 1, name: 'Test Season', slug: 'test-season', is_current: true }
        ]
      }
    ])
  );
  win.localStorage.setItem('boukii_school_id', '1');
  win.localStorage.setItem('boukii_season_id', '1');
  win.localStorage.setItem('boukii_permissions', JSON.stringify([]));
};

describe('App Shell', () => {
  const visitWithAuth = () =>
    cy.visit('/', {
      onBeforeLoad: setupAuth
    });

  beforeEach(() => {
    cy.clearLocalStorage();
  });

  it('persists sidebar collapse across reloads', () => {
    visitWithAuth();
    cy.get('.app-sidebar').should('be.visible').and('not.have.class', 'collapsed');
    
    // Click the collapse button and verify chevron is always visible
    cy.get('.sidebar-header .collapse').should('be.visible').click();
    cy.get('.app-sidebar').should('have.class', 'collapsed');
    cy.get('.sidebar-header .collapse .chev').should('be.visible').and('have.class', 'rot');
    
    // Verify chevron is still clickable when collapsed
    cy.get('.sidebar-header .collapse').should('be.visible').click();
    cy.get('.app-sidebar').should('not.have.class', 'collapsed');
    cy.get('.sidebar-header .collapse .chev').should('be.visible').and('not.have.class', 'rot');
    
    // Collapse again and reload to test persistence
    cy.get('.sidebar-header .collapse').click();
    cy.get('.app-sidebar').should('have.class', 'collapsed');
    cy.reload();
    cy.get('.app-sidebar').should('have.class', 'collapsed');
  });

  it('shows correct badge format when expanding/collapsing sidebar', () => {
    visitWithAuth();
    
    // Initially expanded - badges should show numbers
    cy.get('.app-sidebar').should('not.have.class', 'collapsed');
    cy.get('.nav-item .badge').first().should('contain.text', '3');
    
    // Collapse sidebar - badges should become dots
    cy.get('.sidebar-header .collapse').click();
    cy.get('.app-sidebar').should('have.class', 'collapsed');
    
    // Badge should still be visible but as a dot (no text content in collapsed state)
    cy.get('.nav-item .badge').first().should('be.visible');
    
    // Notifications badge should also switch from number to dot when collapsed
    cy.get('.notifications-btn .badge').should('have.class', 'notification-dot');
    
    // Expand again - badges should show numbers
    cy.get('.sidebar-header .collapse').click();
    cy.get('.app-sidebar').should('not.have.class', 'collapsed');
    cy.get('.nav-item .badge').first().should('contain.text', '3');
    cy.get('.notifications-btn .badge').should('have.class', 'notification-badge');
  });

  it('ensures menus have proper z-index and are not hidden', () => {
    visitWithAuth();
    
    // Open language dropdown
    cy.get('.language-menu .language-toggle').click();
    cy.get('.language-dropdown').should('be.visible').and('have.css', 'z-index', '1000');
    
    // Close language dropdown and open notifications
    cy.get('.language-menu .language-toggle').click();
    cy.get('.notifications-btn').click();
    cy.get('.notifications-dropdown').should('be.visible').and('have.css', 'z-index', '1000');
    
    // Close notifications and open user menu
    cy.get('.notifications-btn').click();
    cy.get('.user-menu .user-trigger').click();
    cy.get('.user-dropdown').should('be.visible').and('have.css', 'z-index', '1000');
  });

  it('persists language switch to English in localStorage', () => {
    visitWithAuth();
    
    // Verify initial state
    cy.get('.language-toggle .language-text').should('contain', 'ES');
    cy.window().its('localStorage').invoke('getItem', 'language').should('be.null');
    
    // Open language dropdown and select English
    cy.get('.language-menu .language-toggle').click();
    cy.get('.language-dropdown .dropdown-option').contains('language.en').click();
    
    // Verify UI updated
    cy.get('.language-toggle .language-text').should('contain', 'EN');
    
    // Verify persistence in localStorage
    cy.window().its('localStorage').invoke('getItem', 'language').should('eq', 'en');
    
    // Verify persistence across reload
    cy.reload();
    cy.get('.language-toggle .language-text').should('contain', 'EN');
    cy.window().its('localStorage').invoke('getItem', 'language').should('eq', 'en');
  });

  it('toggles dark and light theme with persistence', () => {
    visitWithAuth();
    
    // Check initial light theme
    cy.get('html').should('have.attr', 'data-theme', 'light');
    
    // Toggle to dark theme
    const themeBtn = '.app-navbar button[title="theme.toggle"]';
    cy.get(themeBtn).click();
    cy.get('html').should('have.attr', 'data-theme', 'dark');
    
    // Verify persistence across reload
    cy.reload();
    cy.get('html').should('have.attr', 'data-theme', 'dark');
    
    // Toggle back to light
    cy.get(themeBtn).click();
    cy.get('html').should('have.attr', 'data-theme', 'light');
    
    // Verify persistence
    cy.reload();
    cy.get('html').should('have.attr', 'data-theme', 'light');
  });

  it('shows user menu and triggers logout via stub', () => {
    visitWithAuth();
    
    // Stub the logout method
    cy.window().then((win) => {
      const injector = (win as any).ng.getInjector(win.document.querySelector('app-shell'));
      const auth = injector.get(AuthV5Service);
      cy.stub(auth, 'logout').as('logout');
    });
    
    // Open user dropdown
    cy.get('.user-menu .user-trigger').click();
    cy.get('.user-dropdown').should('be.visible');
    
    // Click logout button
    cy.get('.user-dropdown .dropdown-option.danger').click();
    cy.get('@logout').should('have.been.called');
  });

  it('validates accessibility features', () => {
    visitWithAuth();
    
    // Check ARIA attributes on dropdowns
    cy.get('.language-toggle').should('have.attr', 'aria-haspopup', 'menu');
    cy.get('.language-toggle').should('have.attr', 'aria-expanded', 'false');
    
    // Open language dropdown and check expanded state
    cy.get('.language-toggle').click();
    cy.get('.language-toggle').should('have.attr', 'aria-expanded', 'true');
    cy.get('.language-dropdown').should('have.attr', 'role', 'menu');
    cy.get('.language-dropdown button').first().should('have.attr', 'role', 'menuitemradio');
    
    // Check sidebar navigation accessibility
    cy.get('.app-sidebar').should('have.attr', 'role', 'navigation');
    cy.get('.nav-item').should('have.attr', 'role', 'menuitem');
    
    // Check keyboard navigation support (Escape key)
    cy.get('body').type('{esc}');
    cy.get('.language-dropdown').should('not.exist');
  });
});