#!/usr/bin/env node

/**
 * Git Hooks Manager - Boukii Admin V5
 * 
 * Manages Git hooks installation, validation, and troubleshooting
 */

const { execSync } = require('child_process');
const fs = require('fs');
const path = require('path');

class GitHooksManager {
  constructor() {
    this.projectRoot = process.cwd();
    this.huskyDir = path.join(this.projectRoot, '.husky');
    this.hooks = [
      'pre-commit',
      'commit-msg', 
      'prepare-commit-msg',
      'post-commit'
    ];
  }

  /**
   * Main execution method
   */
  async run() {
    const command = process.argv[2];
    
    console.log('🎣 Boukii Git Hooks Manager v1.0.0\n');
    
    try {
      switch (command) {
        case 'install':
          await this.installHooks();
          break;
        case 'validate':
          await this.validateHooks();
          break;
        case 'test':
          await this.testHooks();
          break;
        case 'status':
          await this.showStatus();
          break;
        default:
          this.showHelp();
      }
    } catch (error) {
      console.error('❌ Git hooks operation failed:', error.message);
      process.exit(1);
    }
  }

  /**
   * Install and configure Git hooks
   */
  async installHooks() {
    console.log('🔧 Installing Git hooks...\n');

    // Check if Husky is properly configured
    if (!fs.existsSync(this.huskyDir)) {
      console.log('📦 Initializing Husky...');
      execSync('npx husky init', { cwd: this.projectRoot });
    }

    // Validate hook files
    console.log('📋 Validating hook files...');
    let allValid = true;

    for (const hook of this.hooks) {
      const hookPath = path.join(this.huskyDir, hook);
      if (fs.existsSync(hookPath)) {
        console.log(`  ✅ ${hook} - exists`);
        
        // Make executable
        try {
          execSync(`chmod +x "${hookPath}"`, { cwd: this.projectRoot });
          console.log(`  🔓 ${hook} - made executable`);
        } catch (error) {
          console.log(`  ⚠️ ${hook} - failed to make executable: ${error.message}`);
        }
      } else {
        console.log(`  ❌ ${hook} - missing`);
        allValid = false;
      }
    }

    // Test lint-staged configuration
    console.log('\n🎨 Testing lint-staged configuration...');
    try {
      execSync('npx lint-staged --version', { stdio: 'pipe', cwd: this.projectRoot });
      console.log('  ✅ lint-staged is available');
    } catch (error) {
      console.log('  ❌ lint-staged not found');
      allValid = false;
    }

    // Test commitlint configuration
    console.log('\n💬 Testing commitlint configuration...');
    try {
      execSync('npx commitlint --version', { stdio: 'pipe', cwd: this.projectRoot });
      console.log('  ✅ commitlint is available');
      
      // Check for config file
      const commitlintConfig = path.join(this.projectRoot, 'commitlint.config.js');
      if (fs.existsSync(commitlintConfig)) {
        console.log('  ✅ commitlint.config.js found');
      } else {
        console.log('  ⚠️ commitlint.config.js not found');
      }
    } catch (error) {
      console.log('  ❌ commitlint not found');
      allValid = false;
    }

    console.log('\n' + '='.repeat(50));
    if (allValid) {
      console.log('🎉 Git hooks installation completed successfully!');
      console.log('\n💡 Next steps:');
      console.log('  • Test hooks: npm run hooks:test');
      console.log('  • Make a test commit to verify setup');
    } else {
      console.log('⚠️ Git hooks installation completed with warnings');
      console.log('\n💡 To fix issues:');
      console.log('  • Run: npm run hooks:validate');
      console.log('  • Check missing hook files');
    }
  }

  /**
   * Validate existing hooks
   */
  async validateHooks() {
    console.log('🔍 Validating Git hooks...\n');

    let issuesFound = 0;

    // Check if .git directory exists
    if (!fs.existsSync(path.join(this.projectRoot, '.git'))) {
      console.log('❌ Not a Git repository');
      return;
    }

    // Check Husky installation
    console.log('📦 Checking Husky installation...');
    if (fs.existsSync(this.huskyDir)) {
      console.log('  ✅ .husky directory exists');
    } else {
      console.log('  ❌ .husky directory missing');
      issuesFound++;
    }

    // Check individual hooks
    console.log('\n🎣 Checking hook files...');
    for (const hook of this.hooks) {
      const hookPath = path.join(this.huskyDir, hook);
      
      if (fs.existsSync(hookPath)) {
        const stats = fs.statSync(hookPath);
        const isExecutable = (stats.mode & parseInt('111', 8)) !== 0;
        
        console.log(`  ✅ ${hook} - exists ${isExecutable ? '(executable)' : '(not executable)'}`);
        
        if (!isExecutable) {
          console.log(`    ⚠️ Making ${hook} executable...`);
          try {
            execSync(`chmod +x "${hookPath}"`, { cwd: this.projectRoot });
            console.log(`    ✅ ${hook} is now executable`);
          } catch (error) {
            console.log(`    ❌ Failed to make ${hook} executable`);
            issuesFound++;
          }
        }
      } else {
        console.log(`  ❌ ${hook} - missing`);
        issuesFound++;
      }
    }

    // Check dependencies
    console.log('\n📦 Checking dependencies...');
    const dependencies = [
      { name: 'husky', command: 'npx husky --version' },
      { name: 'lint-staged', command: 'npx lint-staged --version' },
      { name: 'commitlint', command: 'npx commitlint --version' }
    ];

    for (const dep of dependencies) {
      try {
        execSync(dep.command, { stdio: 'pipe', cwd: this.projectRoot });
        console.log(`  ✅ ${dep.name} - available`);
      } catch (error) {
        console.log(`  ❌ ${dep.name} - not available`);
        issuesFound++;
      }
    }

    console.log('\n' + '='.repeat(50));
    if (issuesFound === 0) {
      console.log('🎉 All Git hooks are properly configured!');
    } else {
      console.log(`⚠️ Found ${issuesFound} issues that need attention`);
      console.log('\n💡 To fix issues:');
      console.log('  • Run: npm run hooks:install');
      console.log('  • Install missing dependencies');
    }
  }

  /**
   * Test hooks without making commits
   */
  async testHooks() {
    console.log('🧪 Testing Git hooks...\n');

    // Test lint-staged
    console.log('🎨 Testing lint-staged...');
    try {
      execSync('npx lint-staged --help', { stdio: 'pipe', cwd: this.projectRoot });
      console.log('  ✅ lint-staged command works');
    } catch (error) {
      console.log('  ❌ lint-staged test failed');
    }

    // Test commitlint with sample messages
    console.log('\n💬 Testing commitlint...');
    const testMessages = [
      { msg: 'feat: add new feature', valid: true },
      { msg: 'fix: resolve bug issue', valid: true },
      { msg: 'invalid commit message', valid: false },
      { msg: 'feat!: breaking change', valid: true }
    ];

    for (const test of testMessages) {
      try {
        execSync(`echo "${test.msg}" | npx commitlint`, { 
          stdio: 'pipe', 
          cwd: this.projectRoot 
        });
        console.log(`  ${test.valid ? '✅' : '❌'} "${test.msg}" - ${test.valid ? 'valid' : 'should be invalid'}`);
      } catch (error) {
        console.log(`  ${test.valid ? '❌' : '✅'} "${test.msg}" - ${test.valid ? 'should be valid' : 'invalid'}`);
      }
    }

    // Test format check
    console.log('\n🎨 Testing format verification...');
    try {
      execSync('npm run format:check', { stdio: 'pipe', cwd: this.projectRoot });
      console.log('  ✅ Format check works');
    } catch (error) {
      console.log('  ⚠️ Format check found issues (expected if code needs formatting)');
    }

    console.log('\n🎉 Hook testing completed!');
  }

  /**
   * Show current hooks status
   */
  async showStatus() {
    console.log('📊 Git Hooks Status\n');

    // Git repository status
    const isGitRepo = fs.existsSync(path.join(this.projectRoot, '.git'));
    console.log(`🔧 Git Repository: ${isGitRepo ? '✅ Yes' : '❌ No'}`);

    // Husky status
    const huskyExists = fs.existsSync(this.huskyDir);
    console.log(`🎣 Husky Directory: ${huskyExists ? '✅ Exists' : '❌ Missing'}`);

    if (huskyExists) {
      console.log('\n📋 Hook Files:');
      for (const hook of this.hooks) {
        const hookPath = path.join(this.huskyDir, hook);
        const exists = fs.existsSync(hookPath);
        
        if (exists) {
          const stats = fs.statSync(hookPath);
          const isExecutable = (stats.mode & parseInt('111', 8)) !== 0;
          console.log(`  ${hook}: ✅ ${isExecutable ? '(executable)' : '(not executable)'}`);
        } else {
          console.log(`  ${hook}: ❌ Missing`);
        }
      }
    }

    // Package.json scripts
    console.log('\n📦 Package.json:');
    const packagePath = path.join(this.projectRoot, 'package.json');
    if (fs.existsSync(packagePath)) {
      const packageJson = JSON.parse(fs.readFileSync(packagePath, 'utf8'));
      
      console.log(`  prepare script: ${packageJson.scripts?.prepare ? '✅ Configured' : '❌ Missing'}`);
      console.log(`  lint-staged config: ${packageJson['lint-staged'] ? '✅ Configured' : '❌ Missing'}`);
    }
  }

  /**
   * Show help information
   */
  showHelp() {
    console.log('🎣 Boukii Git Hooks Manager\n');
    console.log('Usage: node scripts/git-hooks-manager.js <command>\n');
    console.log('Commands:');
    console.log('  install   Install and configure Git hooks');
    console.log('  validate  Validate existing hooks');
    console.log('  test      Test hooks without committing');
    console.log('  status    Show current hooks status');
    console.log('\nExamples:');
    console.log('  npm run hooks:install');
    console.log('  npm run hooks:validate');
    console.log('  npm run hooks:test');
    console.log('  npm run hooks:status');
  }
}

// Execute if called directly
if (require.main === module) {
  new GitHooksManager().run();
}

module.exports = GitHooksManager;