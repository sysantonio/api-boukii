# Cypress E2E Tests - Boukii V5

This directory contains end-to-end tests for the Boukii V5 admin panel using Cypress.

## 📁 Test Structure

```
cypress/
├── e2e/                    # E2E test files
│   ├── auth-flow.cy.ts    # Authentication flow tests
│   ├── error-handling.cy.ts # Error scenarios and edge cases
│   └── theme-switching.cy.ts # Theme functionality tests
├── fixtures/               # Test data
├── support/               # Support files and custom commands
│   ├── commands.ts        # Custom Cypress commands
│   └── e2e.ts            # E2E support file
└── README.md             # This file
```

## 🚀 Running Tests

### Local Development

```bash
# Open Cypress Test Runner (interactive mode)
npm run cypress:open

# Run all tests headlessly
npm run cypress:run

# Run tests with specific browser
npm run cypress:run:chrome
npm run cypress:run:firefox
npm run cypress:run:edge

# Run individual test suites
npm run e2e:auth        # Authentication flow tests
npm run e2e:errors      # Error handling tests
npm run e2e:theme       # Theme switching tests

# Run tests with server startup (recommended)
npm run test:e2e        # Starts server + runs tests
```

### CI/CD

```bash
# Run tests in CI mode (with JUnit reporting)
npm run test:e2e:ci
```

## 🧪 Test Categories

### 1. Authentication Flow (`auth-flow.cy.ts`)

Tests the complete authentication workflow:

- ✅ Login form validation
- ✅ Successful login with single school (auto-select)
- ✅ Multiple schools selection flow
- ✅ Navigation between auth states
- ✅ Authentication persistence
- ✅ Logout functionality

**Key scenarios:**
- Valid/invalid credentials
- Single vs multiple schools
- Auto-navigation logic
- Token persistence across reloads

### 2. Error Handling (`error-handling.cy.ts`)

Tests various error scenarios and edge cases:

- ✅ API errors (500, 422, 401, network timeouts)
- ✅ Malformed responses
- ✅ School selection errors
- ✅ Navigation errors (invalid routes, expired tokens)
- ✅ Form validation edge cases
- ✅ Browser compatibility issues

**Key scenarios:**
- Server errors with user-friendly messages
- Network failures and timeouts
- Invalid data handling
- Graceful degradation

### 3. Theme Switching (`theme-switching.cy.ts`)

Tests the light/dark theme functionality:

- ✅ Default theme behavior
- ✅ Theme toggle functionality
- ✅ System preference detection
- ✅ Theme persistence
- ✅ Visual verification
- ✅ Accessibility compliance

**Key scenarios:**
- Light ↔ Dark ↔ System theme cycling
- Theme persistence across navigation
- CSS variable application
- Smooth transitions

## 🛠 Custom Commands

The following custom commands are available for use in tests:

### Authentication Commands
```typescript
cy.login(email, password)              // Login with credentials
cy.loginWithValidCredentials()         // Login with test user
cy.shouldBeOnLoginPage()              // Assert on login page
cy.shouldBeOnSchoolSelectionPage()    // Assert on school selection
cy.shouldBeOnDashboard()              // Assert on dashboard
```

### Navigation Commands
```typescript
cy.waitForAppToLoad()                 // Wait for app initialization
cy.selectFirstSchool()                // Select first school in list
```

### Theme Commands
```typescript
cy.toggleTheme()                      // Toggle light/dark theme
cy.shouldHaveTheme('light'|'dark')    // Assert current theme
```

### API Mocking Commands
```typescript
cy.mockLoginSuccess()                 // Mock successful login
cy.mockLoginError()                   // Mock login error
cy.mockMultipleSchools()             // Mock multiple schools response
```

## 📊 Test Data & Fixtures

Test data is stored in `cypress/fixtures/`:

- `example.json` - Sample test data
- Additional fixtures can be added as needed

### Mock API Responses

Tests use intercepted API calls with realistic mock data:

```typescript
// Example: Mock successful login
cy.intercept('POST', '**/api/v5/auth/login', {
  statusCode: 200,
  body: {
    success: true,
    data: {
      user: { id: 1, name: 'Test User', email: 'test@boukii.com' },
      token: 'mock-jwt-token',
      schools: [/* ... */]
    }
  }
}).as('loginSuccess')
```

## 🎯 Test Selectors

Tests use `data-cy` attributes for reliable element selection:

```html
<!-- Login form -->
<form data-cy="login-form">
  <input data-cy="email-input" />
  <input data-cy="password-input" />
  <button data-cy="login-button">Login</button>
</form>

<!-- Theme toggle -->
<button data-cy="theme-toggle">Toggle Theme</button>

<!-- School selection -->
<div data-cy="school-selection">
  <button data-cy="school-item">School Name</button>
</div>
```

## 🔧 Configuration

### Environment Variables

```typescript
// cypress.config.ts
env: {
  apiUrl: 'http://api-boukii.test',  // Backend API URL
  coverage: false                     // Code coverage collection
}
```

### CI/CD Configuration

Tests are configured to run in GitHub Actions with:

- ✅ Multiple browsers (Chrome, Firefox, Edge)
- ✅ Automatic retries for flaky tests
- ✅ Video recording on failures
- ✅ Screenshot capture
- ✅ JUnit test reporting
- ✅ Artifact upload for debugging

## 📈 Best Practices

### Writing Tests

1. **Use descriptive test names**
   ```typescript
   it('should redirect to dashboard after selecting single school')
   ```

2. **Use custom commands for reusable actions**
   ```typescript
   cy.loginWithValidCredentials()
   cy.shouldBeOnDashboard()
   ```

3. **Mock API calls for predictable tests**
   ```typescript
   cy.mockLoginSuccess()
   cy.wait('@loginSuccess')
   ```

4. **Test both happy path and error scenarios**
   ```typescript
   describe('Login Success', () => { /* ... */ })
   describe('Login Errors', () => { /* ... */ })
   ```

### Debugging Tests

1. **Run in headed mode**
   ```bash
   npm run cypress:run:headed
   ```

2. **Use interactive mode**
   ```bash
   npm run cypress:open
   ```

3. **Add debugging commands**
   ```typescript
   cy.pause()          // Pause test execution
   cy.debug()          // Debug current subject
   cy.screenshot()     // Take screenshot
   ```

## 🚨 Troubleshooting

### Common Issues

1. **Tests timeout waiting for elements**
   - Increase `defaultCommandTimeout` in config
   - Use proper `data-cy` selectors
   - Wait for API calls with `cy.wait('@alias')`

2. **API intercepts not working**
   - Check URL patterns match exactly
   - Ensure intercepts are set before navigation
   - Use network tab to verify requests

3. **Theme tests failing**
   - Verify CSS variables are properly applied
   - Check that theme persistence works
   - Ensure `data-theme` attribute is set

4. **Authentication state issues**
   - Clear localStorage between tests
   - Mock API responses consistently
   - Verify token storage and retrieval

### Getting Help

- Check Cypress documentation: https://docs.cypress.io/
- Review test logs and screenshots in CI artifacts
- Use browser dev tools during interactive runs
- Add `cy.log()` statements for debugging

## 📝 Contributing

When adding new tests:

1. Follow existing patterns and naming conventions
2. Add appropriate `data-cy` selectors to components
3. Use custom commands for reusable functionality
4. Include both success and error scenarios
5. Update this README if adding new test categories