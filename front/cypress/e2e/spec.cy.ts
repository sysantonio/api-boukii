describe('Basic Application Test', () => {
  it('Visits the initial project page', () => {
    cy.visit('/');
    cy.get('body').should('exist');
    // Should redirect to auth or dashboard depending on authentication
    cy.url().should((url) => {
      expect(url).to.satisfy((currentUrl) => {
        return currentUrl.includes('/auth') || currentUrl.includes('/dashboard');
      });
    });
  });
});
