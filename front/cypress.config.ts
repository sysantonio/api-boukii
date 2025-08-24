import { defineConfig } from 'cypress';

export default defineConfig({
  e2e: {
    baseUrl: 'http://localhost:4200',
    specPattern: 'cypress/e2e/**/*.cy.ts',
    supportFile: 'cypress/support/e2e.ts',
    screenshotOnRunFailure: true,
    video: true,
    retries: { runMode: 2, openMode: 0 },
    defaultCommandTimeout: 12000,
    chromeWebSecurity: false,
    setupNodeEvents(on, config) {
      // Expose console logs and tables for CI debugging
      on('task', {
        log(message) {
          console.log(message);
          return null;
        },
        table(data) {
          console.table(data);
          return null;
        }
      });
      return config;
    },
  },
});
