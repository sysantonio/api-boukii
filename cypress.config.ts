import { defineConfig } from 'cypress'

export default defineConfig({
  e2e: {
    baseUrl: 'http://localhost:4200',
    supportFile: 'cypress/support/e2e.ts',
    specPattern: 'cypress/e2e/**/*.cy.ts',
    videosFolder: 'cypress/videos',
    screenshotsFolder: 'cypress/screenshots',
    fixturesFolder: 'cypress/fixtures',
    video: true,
    screenshot: true,
    videoUploadOnPasses: false, // Only upload videos for failed tests in CI
    viewportWidth: 1280,
    viewportHeight: 720,
    defaultCommandTimeout: 10000,
    requestTimeout: 10000,
    responseTimeout: 10000,
    retries: {
      runMode: 2, // Retry failed tests in CI
      openMode: 0  // Don't retry in interactive mode
    },
    env: {
      apiUrl: 'http://api-boukii.test',
      coverage: false // Enable code coverage in CI
    },
    setupNodeEvents(on, config) {
      // CI-specific configuration
      if (config.isCI) {
        config.video = false // Disable video in CI to save space unless needed
        config.screenshotOnRunFailure = true
      }
      
      // Code coverage plugin setup (if needed)
      // require('@cypress/code-coverage/task')(on, config)
      
      return config
    }
  },
  
  component: {
    devServer: {
      framework: 'angular',
      bundler: 'webpack',
    },
    specPattern: '**/*.cy.ts'
  }
})