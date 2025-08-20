/** @type {import('eslint').Linter.FlatConfig[]} */
const tseslint = require('typescript-eslint');

module.exports = [
  {
    ignores: [
      'dist/**',
      'coverage/**',
      'storybook-static/**',
      'cypress/**',
      '**/*.mdx',
      'node_modules/**',
      '.storybook/**',
      'cypress.config.ts',
      'jest.config.cjs',
      'src/environments/**'
    ]
  },
  {
    files: ['src/**/*.ts'],
    languageOptions: {
      parser: tseslint.parser,
      parserOptions: {
        ecmaVersion: 'latest',
        sourceType: 'module'
      }
    },
    plugins: { '@typescript-eslint': tseslint.plugin },
    rules: {
      '@typescript-eslint/no-unused-vars': [
        'error',
        {
          argsIgnorePattern: '^_',
          varsIgnorePattern: '^_',
          caughtErrorsIgnorePattern: '^_'
        }
      ]
    }
  }
];
