declare global {
  namespace Cypress {
    interface Chainable {
      login(): Chainable<void>;
      selectSchoolAndSeason(): Chainable<void>;
      setTheme(theme:'light'|'dark'): Chainable<void>;
      shouldBeOnSchoolSelectionPage(): Chainable<void>;
      shouldBeOnDashboard(): Chainable<void>;
      shouldHaveTheme(theme: 'light'|'dark'): Chainable<void>;
      toggleTheme(): Chainable<void>;
      mockLoginSuccess(): Chainable<void>;
      mockMultipleSchools(): Chainable<void>;
      selectFirstSchool(): Chainable<void>;
      tab(): Chainable<void>;
    }
  }
}
Cypress.Commands.add('login', () => {
  cy.intercept('POST','/api/v5/auth/login',{
    statusCode:200,
    body:{access_token:'fake',token_type:'Bearer',expires_in:3600,
      user:{id:1,name:'Test User',email:'test@boukii.dev'}}
  }).as('loginApi');
  cy.visit('/login');
  cy.get('[data-testid="email"]').type('test@boukii.dev');
  cy.get('[data-testid="password"]').type('Password_123!');
  cy.get('[data-testid="submit"]').click();
  cy.wait('@loginApi');
});
Cypress.Commands.add('selectSchoolAndSeason', () => {
  cy.visit('/select-school');
  cy.wait(['@schools','@seasons']);
  cy.get('[data-testid="school-card"]').first().click();
  cy.url().should('include','/select-season');
  cy.get('[data-testid="season-card"]').first().click();
});
Cypress.Commands.add('setTheme',(theme:'light'|'dark')=>{
  window.localStorage.setItem('theme',theme);
  document.body.dataset.theme = theme;
});

Cypress.Commands.add('shouldBeOnSchoolSelectionPage', () => {
  cy.url().should('include', '/select-school');
  cy.get('[data-cy="school-selection"]').should('be.visible');
});

Cypress.Commands.add('shouldBeOnDashboard', () => {
  cy.url().should('include', '/dashboard');
  cy.get('[data-cy="dashboard"]').should('be.visible');
});

Cypress.Commands.add('shouldHaveTheme', (theme: 'light'|'dark') => {
  cy.get('html').should('have.attr', 'data-theme', theme);
});

Cypress.Commands.add('toggleTheme', () => {
  cy.get('[data-cy="theme-toggle"]').click();
});

Cypress.Commands.add('mockLoginSuccess', () => {
  cy.intercept('POST', '**/api/v5/auth/login', {
    statusCode: 200,
    body: {
      success: true,
      data: {
        schools: [{
          id: 1,
          name: 'Test School'
        }],
        temp_token: 'temp-token-123'
      }
    }
  }).as('loginSuccess');
});

Cypress.Commands.add('mockMultipleSchools', () => {
  cy.intercept('POST', '**/api/v5/auth/login', {
    statusCode: 200,
    body: {
      success: true,
      data: {
        schools: [
          { id: 1, name: 'School 1' },
          { id: 2, name: 'School 2' }
        ],
        temp_token: 'temp-token-123'
      }
    }
  }).as('loginMultipleSchools');
});

Cypress.Commands.add('selectFirstSchool', () => {
  cy.get('[data-cy="school-item"]').first().click();
});

Cypress.Commands.add('tab', { prevSubject: 'element' }, (subject) => {
  cy.wrap(subject).trigger('keydown', { key: 'Tab' });
  return cy.focused();
});

export {};
