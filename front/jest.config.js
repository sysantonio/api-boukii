/** @type {import('jest').Config} */
module.exports = {
  preset: 'jest-preset-angular',
  setupFilesAfterEnv: ['<rootDir>/setup-jest.ts'],
  testMatch: ['<rootDir>/src/**/__tests__/**/*.(js|ts)', '<rootDir>/src/**/*.(test|spec).(js|ts)'],
  collectCoverageFrom: [
    'src/**/*.ts',
    '!src/**/*.d.ts',
    '!src/**/index.ts',
    '!src/**/*.module.ts',
    '!src/**/*.config.ts',
    '!src/main.ts',
    '!src/polyfills.ts',
  ],
  coverageDirectory: 'coverage',
  coverageReporters: ['html', 'text-summary', 'cobertura'],
  moduleNameMapper: {
    '^@core/(.*)$': '<rootDir>/src/app/core/$1',
    '^@shared/(.*)$': '<rootDir>/src/app/shared/$1',
    '^@ui/(.*)$': '<rootDir>/src/app/ui/$1',
    '^@features/(.*)$': '<rootDir>/src/app/features/$1',
    '^@state/(.*)$': '<rootDir>/src/app/state/$1',
    '^@assets/(.*)$': '<rootDir>/src/assets/$1',
    '^@environments/(.*)$': '<rootDir>/src/environments/$1',
  },
  transform: {
    '^.+\\.(ts|js|html)$': [
      'jest-preset-angular',
      {
        tsconfig: 'tsconfig.spec.json',
        stringifyContentPathRegex: '\\.html$',
      },
    ],
  },
  testEnvironment: 'jsdom',
  restoreMocks: true,
  clearMocks: true,
};
