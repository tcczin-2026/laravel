const { defineConfig } = require('cypress');

module.exports = defineConfig({
    e2e: {
        // servidor do ambiente "cypress" (SQLite próprio) — veja npm run test:e2e
        baseUrl: 'http://127.0.0.1:8010',
        specPattern: 'cypress/e2e/**/*.cy.js',
        supportFile: 'cypress/support/e2e.js',
        viewportWidth: 1366,
        viewportHeight: 900,
        video: false,
        screenshotOnRunFailure: true,
    },
});
