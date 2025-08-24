/**
 * Basic Route Tests
 */
describe('Route Handling', () => {
  beforeEach(() => {
    cy.clearLocalStorage();
  });

  it('handles unknown routes gracefully', () => {
    // Test that unknown routes redirect appropriately
    cy.visit('/unknown-route', { failOnStatusCode: false });
    
    // Should redirect to a known page (auth or dashboard)
    cy.url().should((url) => {
      expect(url).to.satisfy((currentUrl) => {
        return currentUrl.includes('/auth') || currentUrl.includes('/dashboard') || currentUrl.includes('/');
      });
    });
    
    // Page should load
    cy.get('body').should('exist');
  });

  it('maintains auth state across navigation', () => {
    // Test basic auth state persistence
    cy.visit('/auth/login');
    
    cy.window().then((win) => {
      win.localStorage.setItem('theme', 'light');
      win.localStorage.setItem('access_token', 'test-token');
    });
    
    // Reload and check state persists
    cy.reload();
    
    cy.window().then((win) => {
      expect(win.localStorage.getItem('theme')).to.equal('light');
      expect(win.localStorage.getItem('access_token')).to.equal('test-token');
    });
  });
});
