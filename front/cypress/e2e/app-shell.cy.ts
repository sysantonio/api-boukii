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
    cy.get('.sidebar-header .collapse').click();
    cy.get('.app-sidebar').should('have.class', 'collapsed');
    cy.reload();
    cy.get('.app-sidebar').should('have.class', 'collapsed');
  });

  it('persists language switch to English after reload', () => {
    visitWithAuth();
    cy.get('.language-menu .language-toggle').click();
    cy.get('.language-dropdown .dropdown-option').contains('English').click();
    cy.get('.language-toggle .language-text').should('contain', 'EN');
    cy.reload();
    cy.get('.language-toggle .language-text').should('contain', 'EN');
  });

  it('toggles dark and light theme with persistence', () => {
    visitWithAuth();
    const themeBtn = '.app-navbar > button.icon-btn:not(.language-toggle):not(.notifications-btn)';
    cy.get('html').should('have.attr', 'data-theme', 'light');
    cy.get(themeBtn).click();
    cy.get('html').should('have.attr', 'data-theme', 'dark');
    cy.reload();
    cy.get('html').should('have.attr', 'data-theme', 'dark');
    cy.get(themeBtn).click();
    cy.get('html').should('have.attr', 'data-theme', 'light');
    cy.reload();
    cy.get('html').should('have.attr', 'data-theme', 'light');
  });

  it('shows user menu and triggers logout via stub', () => {
    visitWithAuth();
    cy.window().then((win) => {
      const injector = (win as any).ng.getInjector(win.document.querySelector('app-shell'));
      const auth = injector.get(AuthV5Service);
      cy.stub(auth, 'logout').as('logout');
    });
    cy.get('.user-menu .user-trigger').click();
    cy.get('.user-dropdown').should('be.visible');
    cy.get('.user-dropdown .dropdown-option.danger').click();
    cy.get('@logout').should('have.been.called');
  });
});

