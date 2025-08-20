/* eslint-disable */
import type { Config } from 'jest';
import { pathsToModuleNameMapper } from 'ts-jest';
import { compilerOptions } from './tsconfig.spec.json';
import 'jest-preset-angular/setup-jest';

const config: Config = {
  preset: 'jest-preset-angular',
  testEnvironment: 'jsdom',
  setupFilesAfterEnv: ['<rootDir>/src/test-setup.ts'],
  transform: {
    '^.+\\.(ts|js|html)$': 'ts-jest'
  },
  moduleNameMapper: pathsToModuleNameMapper(compilerOptions.paths || {}, { prefix: '<rootDir>/' }),
  collectCoverageFrom: [
    'src/app/**/*.ts',
    '!src/main.ts',
    '!src/environments/**',
    '!src/polyfills.ts'
  ],
  coverageDirectory: 'coverage',
  coverageReporters: ['lcov', 'text-summary'],
  testMatch: ['**/?(*.)+(spec).ts']
};

export default config;
