import { defineConfig } from 'cypress';

export default defineConfig({
  e2e: {
    baseUrl: 'http://localhost:4200',
    defaultCommandTimeout: 8000,
    pageLoadTimeout: 60000,
    viewportWidth: 1280,
    viewportHeight: 800,
    retries: { runMode: 2, openMode: 0 },
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
