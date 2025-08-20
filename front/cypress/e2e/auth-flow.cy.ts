/**
 * Authentication gateway flow: login → school → season → dashboard
 */
describe('Authentication Gate Flow', () => {
  beforeEach(() => {
    cy.clearLocalStorage();
    cy.clearCookies();

    cy.intercept('POST', '**/api/v5/auth/login', {
      fixture: 'auth/login.json'
    }).as('login');

    cy.intercept('POST', '**/api/v5/auth/select-school', {
      fixture: 'auth/select-school.json'
    }).as('selectSchool');
  });

  it('logs in and reaches dashboard after selecting school and season', () => {
    cy.visit('/auth/login');
    cy.get('[data-cy=email-input]').type('test@boukii.com');
    cy.get('[data-cy=password-input]').type('password123');
    cy.get('[data-cy=login-button]').click();

    cy.wait('@login');
    cy.shouldBeOnSchoolSelectionPage();

    cy.get('[data-cy=school-item]').first().click();
    cy.wait('@selectSchool');

    cy.shouldBeOnDashboard();
    cy.window().then(win => {
      expect(win.localStorage.getItem('boukii_season_id')).to.equal('100');
    });
  });
});
