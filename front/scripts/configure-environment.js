#!/usr/bin/env node

/**
 * Environment Configuration Script
 * Configures the application for different environments during build time
 */

const fs = require('fs');
const path = require('path');

// Get environment from command line argument or default to development
const environment = process.argv[2] || 'development';
const validEnvironments = ['local', 'development', 'develop', 'staging', 'production', 'test'];

if (!validEnvironments.includes(environment)) {
  console.error(`Invalid environment: ${environment}`);
  console.error(`Valid environments: ${validEnvironments.join(', ')}`);
  process.exit(1);
}

console.log(`🔧 Configuring environment: ${environment}`);

// Paths
const configDir = path.join(__dirname, '../src/assets/config');
const sourceConfig = path.join(configDir, `runtime-config.${environment}.json`);
const targetConfig = path.join(configDir, 'runtime-config.json');

try {
  // Check if source config exists
  if (!fs.existsSync(sourceConfig)) {
    console.warn(`⚠️  Config file not found: ${sourceConfig}`);
    console.log('Using default development configuration');

    // Use development config as fallback
    const fallbackConfig = path.join(configDir, 'runtime-config.development.json');
    if (fs.existsSync(fallbackConfig)) {
      fs.copyFileSync(fallbackConfig, targetConfig);
    } else {
      console.error('❌ No fallback configuration found');
      process.exit(1);
    }
  } else {
    // Copy environment-specific config to runtime config
    fs.copyFileSync(sourceConfig, targetConfig);
    console.log(`✅ Copied ${sourceConfig} to ${targetConfig}`);
  }

  // Validate the configuration
  const configContent = fs.readFileSync(targetConfig, 'utf8');
  const config = JSON.parse(configContent);

  // Basic validation
  if (!config.name || !config.type || !config.version) {
    console.error('❌ Invalid configuration: missing required fields');
    process.exit(1);
  }

  if (config.type !== environment) {
    console.warn(
      `⚠️  Configuration type (${config.type}) doesn't match environment (${environment})`
    );
  }

  // Environment-specific validations
  if (environment === 'production') {
    if (!config.production) {
      console.error('❌ Production environment must have production flag set to true');
      process.exit(1);
    }

    if (config.features?.enableDebugMode || config.debug) {
      console.warn('⚠️  Debug mode is enabled in production');
    }

    if (!config.monitoring?.enabled && !config.features?.enablePerformanceMonitoring) {
      console.warn('⚠️  Monitoring is disabled in production');
    }
  }

  if (environment === 'development') {
    if (config.production) {
      console.warn('⚠️  Production flag is set in development environment');
    }
  }

  console.log(`✅ Environment configuration complete`);
  console.log(`📝 Configuration summary:`);
  console.log(`   Name: ${config.name}`);
  console.log(`   Type: ${config.type}`);
  console.log(`   Version: ${config.version}`);
  console.log(`   Production: ${config.production}`);
  console.log(`   API Base URL: ${config.api.baseUrl}`);
  console.log(`   Debug Mode: ${config.features?.enableDebugMode || config.debug || false}`);
  console.log(`   Monitoring: ${config.monitoring || config.features?.enablePerformanceMonitoring || false}`);
} catch (error) {
  console.error('❌ Error configuring environment:', error.message);
  process.exit(1);
}
