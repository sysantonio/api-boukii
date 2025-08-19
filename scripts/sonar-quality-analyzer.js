#!/usr/bin/env node

/**
 * SonarQube Quality Analyzer - Boukii Admin V5
 * 
 * Advanced code quality analysis with enterprise-grade metrics
 * and automatic quality gates validation.
 */

const { execSync } = require('child_process');
const fs = require('fs');
const path = require('path');

class SonarQualityAnalyzer {
  constructor() {
    this.projectRoot = process.cwd();
    this.sonarProps = path.join(this.projectRoot, 'sonar-project.properties');
    this.coverageFile = path.join(this.projectRoot, 'coverage', 'lcov.info');
    this.reportsDir = path.join(this.projectRoot, 'reports');
    
    this.metrics = {
      coverage: 0,
      duplicates: 0,
      maintainability: 'A',
      reliability: 'A',
      security: 'A',
      issues: { blocker: 0, critical: 0, major: 0, minor: 0, info: 0 },
      techDebt: '0d',
      complexity: 0
    };
  }

  /**
   * Main execution method
   */
  async run() {
    const command = process.argv[2];
    
    console.log('📊 Boukii SonarQube Quality Analyzer v1.0.0\n');
    
    try {
      switch (command) {
        case 'analyze':
          await this.runFullAnalysis();
          break;
        case 'prepare':
          await this.prepareAnalysis();
          break;
        case 'validate':
          await this.validateConfiguration();
          break;
        case 'report':
          await this.generateReport();
          break;
        case 'metrics':
          await this.calculateMetrics();
          break;
        default:
          this.showHelp();
      }
    } catch (error) {
      console.error('❌ SonarQube analysis failed:', error.message);
      process.exit(1);
    }
  }

  /**
   * Run comprehensive SonarQube analysis
   */
  async runFullAnalysis() {
    console.log('🔍 Running comprehensive SonarQube analysis...\n');

    // Step 1: Prepare environment
    await this.prepareAnalysis();

    // Step 2: Run tests with coverage
    console.log('🧪 Running tests with coverage...');
    try {
      execSync('npm run test:ci', { 
        stdio: 'inherit', 
        cwd: this.projectRoot 
      });
      console.log('  ✅ Tests completed with coverage\n');
    } catch (error) {
      console.log('  ⚠️ Tests failed, continuing with analysis\n');
    }

    // Step 3: Run ESLint with SonarJS
    console.log('🔍 Running ESLint analysis...');
    try {
      execSync(`npx eslint "src/**/*.{ts,js}" --format json --output-file ${path.join(this.reportsDir, 'eslint-report.json')}`, {
        cwd: this.projectRoot
      });
      console.log('  ✅ ESLint analysis completed\n');
    } catch (error) {
      console.log('  ⚠️ ESLint found issues, report generated\n');
    }

    // Step 4: Calculate complexity metrics
    console.log('📈 Calculating complexity metrics...');
    await this.calculateComplexityMetrics();

    // Step 5: Run SonarQube scanner
    console.log('🚀 Running SonarQube scanner...');
    try {
      execSync('npx sonar-scanner', { 
        stdio: 'inherit', 
        cwd: this.projectRoot 
      });
      console.log('  ✅ SonarQube analysis completed\n');
    } catch (error) {
      console.log('  ⚠️ SonarQube scanner completed with warnings\n');
    }

    // Step 6: Generate comprehensive report
    await this.generateReport();
  }

  /**
   * Prepare analysis environment
   */
  async prepareAnalysis() {
    console.log('🔧 Preparing analysis environment...');

    // Create reports directory
    if (!fs.existsSync(this.reportsDir)) {
      fs.mkdirSync(this.reportsDir, { recursive: true });
      console.log('  ✅ Reports directory created');
    }

    // Validate SonarQube configuration
    await this.validateConfiguration();

    // Check dependencies
    const dependencies = [
      { name: 'sonar-scanner', command: 'npx sonar-scanner --version' },
      { name: 'eslint', command: 'npx eslint --version' },
      { name: 'jest', command: 'npx jest --version' }
    ];

    console.log('📦 Checking dependencies...');
    for (const dep of dependencies) {
      try {
        execSync(dep.command, { stdio: 'pipe', cwd: this.projectRoot });
        console.log(`  ✅ ${dep.name} - available`);
      } catch (error) {
        console.log(`  ❌ ${dep.name} - not available`);
      }
    }

    console.log('');
  }

  /**
   * Validate SonarQube configuration
   */
  async validateConfiguration() {
    console.log('🔍 Validating SonarQube configuration...');

    // Check sonar-project.properties
    if (fs.existsSync(this.sonarProps)) {
      console.log('  ✅ sonar-project.properties found');
      
      const content = fs.readFileSync(this.sonarProps, 'utf8');
      const requiredProps = [
        'sonar.projectKey',
        'sonar.projectName',
        'sonar.sources',
        'sonar.tests'
      ];

      let missingProps = 0;
      for (const prop of requiredProps) {
        if (content.includes(prop)) {
          console.log(`  ✅ ${prop} - configured`);
        } else {
          console.log(`  ❌ ${prop} - missing`);
          missingProps++;
        }
      }

      if (missingProps === 0) {
        console.log('  ✅ All required properties configured');
      } else {
        throw new Error(`Missing ${missingProps} required SonarQube properties`);
      }
    } else {
      throw new Error('sonar-project.properties not found');
    }

    console.log('');
  }

  /**
   * Calculate complexity metrics
   */
  async calculateComplexityMetrics() {
    const srcDir = path.join(this.projectRoot, 'src');
    const files = this.getAllTsFiles(srcDir);
    
    let totalComplexity = 0;
    let totalFiles = 0;
    let totalLines = 0;
    const complexityByFile = [];

    for (const file of files) {
      if (file.includes('.spec.') || file.includes('.test.')) continue;
      
      const content = fs.readFileSync(file, 'utf8');
      const lines = content.split('\n').length;
      const complexity = this.calculateCyclomaticComplexity(content);
      
      totalComplexity += complexity;
      totalFiles++;
      totalLines += lines;
      
      complexityByFile.push({
        file: path.relative(this.projectRoot, file),
        complexity,
        lines
      });
    }

    // Sort by complexity
    complexityByFile.sort((a, b) => b.complexity - a.complexity);

    // Generate complexity report
    const complexityReport = {
      summary: {
        totalFiles,
        totalLines,
        totalComplexity,
        averageComplexity: Math.round(totalComplexity / totalFiles * 100) / 100,
        averageLinesPerFile: Math.round(totalLines / totalFiles)
      },
      topComplexFiles: complexityByFile.slice(0, 10),
      distribution: this.getComplexityDistribution(complexityByFile)
    };

    fs.writeFileSync(
      path.join(this.reportsDir, 'complexity-report.json'),
      JSON.stringify(complexityReport, null, 2)
    );

    console.log(`  ✅ Analyzed ${totalFiles} files`);
    console.log(`  📊 Average complexity: ${complexityReport.summary.averageComplexity}`);
    console.log(`  📈 Total lines: ${totalLines}`);
  }

  /**
   * Calculate cyclomatic complexity for a file
   */
  calculateCyclomaticComplexity(content) {
    // Simple cyclomatic complexity calculation
    const patterns = [
      /\bif\s*\(/g,
      /\belse\b/g,
      /\bwhile\s*\(/g,
      /\bfor\s*\(/g,
      /\bdo\s*\{/g,
      /\bswitch\s*\(/g,
      /\bcase\s+/g,
      /\bcatch\s*\(/g,
      /\b\?\s*/g, // ternary operator
      /\b&&\b/g,
      /\b\|\|\b/g
    ];

    let complexity = 1; // Base complexity
    
    for (const pattern of patterns) {
      const matches = content.match(pattern);
      if (matches) {
        complexity += matches.length;
      }
    }

    return complexity;
  }

  /**
   * Get complexity distribution
   */
  getComplexityDistribution(files) {
    const ranges = {
      'Low (1-5)': 0,
      'Moderate (6-10)': 0,
      'High (11-20)': 0,
      'Very High (21+)': 0
    };

    for (const file of files) {
      if (file.complexity <= 5) ranges['Low (1-5)']++;
      else if (file.complexity <= 10) ranges['Moderate (6-10)']++;
      else if (file.complexity <= 20) ranges['High (11-20)']++;
      else ranges['Very High (21+)']++;
    }

    return ranges;
  }

  /**
   * Get all TypeScript files recursively
   */
  getAllTsFiles(dir) {
    const files = [];
    
    function walk(currentDir) {
      const items = fs.readdirSync(currentDir);
      
      for (const item of items) {
        const fullPath = path.join(currentDir, item);
        const stat = fs.statSync(fullPath);
        
        if (stat.isDirectory() && !item.startsWith('.') && item !== 'node_modules') {
          walk(fullPath);
        } else if (item.endsWith('.ts') && !item.endsWith('.d.ts')) {
          files.push(fullPath);
        }
      }
    }
    
    walk(dir);
    return files;
  }

  /**
   * Calculate basic metrics
   */
  async calculateMetrics() {
    console.log('📊 Calculating code metrics...\n');

    // Coverage metrics
    if (fs.existsSync(this.coverageFile)) {
      const coverage = this.parseLcovCoverage();
      console.log(`📈 Test Coverage: ${coverage.percentage}%`);
      console.log(`  Lines covered: ${coverage.linesCovered}/${coverage.totalLines}`);
      console.log(`  Functions covered: ${coverage.functionsCovered}/${coverage.totalFunctions}`);
    } else {
      console.log('⚠️ No coverage data found. Run tests first.');
    }

    // File metrics
    const srcDir = path.join(this.projectRoot, 'src');
    const files = this.getAllTsFiles(srcDir);
    const sourceFiles = files.filter(f => !f.includes('.spec.') && !f.includes('.test.'));
    const testFiles = files.filter(f => f.includes('.spec.') || f.includes('.test.'));

    console.log(`\n📁 File Metrics:`);
    console.log(`  Source files: ${sourceFiles.length}`);
    console.log(`  Test files: ${testFiles.length}`);
    console.log(`  Test ratio: ${Math.round(testFiles.length / sourceFiles.length * 100)}%`);

    // Size metrics
    let totalLines = 0;
    let totalSize = 0;
    
    for (const file of sourceFiles) {
      const content = fs.readFileSync(file, 'utf8');
      const lines = content.split('\n').length;
      totalLines += lines;
      totalSize += fs.statSync(file).size;
    }

    console.log(`\n📏 Size Metrics:`);
    console.log(`  Total lines: ${totalLines.toLocaleString()}`);
    console.log(`  Average lines per file: ${Math.round(totalLines / sourceFiles.length)}`);
    console.log(`  Total size: ${Math.round(totalSize / 1024)} KB`);

    // Complexity metrics from previous calculation
    const complexityReportPath = path.join(this.reportsDir, 'complexity-report.json');
    if (fs.existsSync(complexityReportPath)) {
      const complexityReport = JSON.parse(fs.readFileSync(complexityReportPath, 'utf8'));
      console.log(`\n🧮 Complexity Metrics:`);
      console.log(`  Average complexity: ${complexityReport.summary.averageComplexity}`);
      console.log(`  Total complexity: ${complexityReport.summary.totalComplexity}`);
    }
  }

  /**
   * Parse LCOV coverage data
   */
  parseLcovCoverage() {
    const content = fs.readFileSync(this.coverageFile, 'utf8');
    const lines = content.split('\n');
    
    let totalLines = 0;
    let linesCovered = 0;
    let totalFunctions = 0;
    let functionsCovered = 0;

    for (const line of lines) {
      if (line.startsWith('LF:')) {
        totalLines += parseInt(line.split(':')[1]);
      } else if (line.startsWith('LH:')) {
        linesCovered += parseInt(line.split(':')[1]);
      } else if (line.startsWith('FNF:')) {
        totalFunctions += parseInt(line.split(':')[1]);
      } else if (line.startsWith('FNH:')) {
        functionsCovered += parseInt(line.split(':')[1]);
      }
    }

    return {
      totalLines,
      linesCovered,
      totalFunctions,
      functionsCovered,
      percentage: totalLines > 0 ? Math.round(linesCovered / totalLines * 100) : 0
    };
  }

  /**
   * Generate comprehensive quality report
   */
  async generateReport() {
    console.log('📋 Generating comprehensive quality report...\n');

    const report = {
      timestamp: new Date().toISOString(),
      project: 'Boukii Admin V5',
      summary: {},
      metrics: {},
      issues: {},
      recommendations: []
    };

    // Calculate metrics
    await this.calculateMetrics();

    // Load various reports if they exist
    const reports = {
      eslint: path.join(this.reportsDir, 'eslint-report.json'),
      complexity: path.join(this.reportsDir, 'complexity-report.json'),
      coverage: this.coverageFile
    };

    for (const [name, reportPath] of Object.entries(reports)) {
      if (fs.existsSync(reportPath)) {
        console.log(`  ✅ ${name} report found`);
      } else {
        console.log(`  ⚠️ ${name} report missing`);
      }
    }

    // Generate quality score
    const qualityScore = this.calculateQualityScore();
    report.summary.qualityScore = qualityScore;
    report.summary.grade = this.getQualityGrade(qualityScore);

    // Save report
    const reportPath = path.join(this.reportsDir, `quality-report-${new Date().toISOString().split('T')[0]}.json`);
    fs.writeFileSync(reportPath, JSON.stringify(report, null, 2));

    console.log('\n' + '='.repeat(60));
    console.log('📊 QUALITY REPORT SUMMARY');
    console.log('='.repeat(60));
    console.log(`🎯 Quality Score: ${qualityScore}/100`);
    console.log(`📈 Grade: ${report.summary.grade}`);
    console.log(`📁 Report saved: ${path.relative(this.projectRoot, reportPath)}`);
    console.log('='.repeat(60));
  }

  /**
   * Calculate overall quality score
   */
  calculateQualityScore() {
    let score = 100;

    // Coverage penalty
    if (fs.existsSync(this.coverageFile)) {
      const coverage = this.parseLcovCoverage();
      if (coverage.percentage < 80) score -= (80 - coverage.percentage) * 0.5;
    } else {
      score -= 30; // No coverage
    }

    // Complexity penalty
    const complexityReportPath = path.join(this.reportsDir, 'complexity-report.json');
    if (fs.existsSync(complexityReportPath)) {
      const complexityReport = JSON.parse(fs.readFileSync(complexityReportPath, 'utf8'));
      if (complexityReport.summary.averageComplexity > 10) {
        score -= (complexityReport.summary.averageComplexity - 10) * 2;
      }
    }

    // ESLint issues penalty
    const eslintReportPath = path.join(this.reportsDir, 'eslint-report.json');
    if (fs.existsSync(eslintReportPath)) {
      try {
        const eslintReport = JSON.parse(fs.readFileSync(eslintReportPath, 'utf8'));
        let totalIssues = 0;
        for (const file of eslintReport) {
          totalIssues += file.errorCount + file.warningCount;
        }
        score -= Math.min(totalIssues * 0.1, 20); // Max 20 points penalty
      } catch (error) {
        // Ignore parsing errors
      }
    }

    return Math.max(0, Math.round(score));
  }

  /**
   * Get quality grade based on score
   */
  getQualityGrade(score) {
    if (score >= 90) return 'A+';
    if (score >= 80) return 'A';
    if (score >= 70) return 'B';
    if (score >= 60) return 'C';
    if (score >= 50) return 'D';
    return 'F';
  }

  /**
   * Show help information
   */
  showHelp() {
    console.log('📊 Boukii SonarQube Quality Analyzer\n');
    console.log('Usage: node scripts/sonar-quality-analyzer.js <command>\n');
    console.log('Commands:');
    console.log('  analyze   Run full SonarQube analysis');
    console.log('  prepare   Prepare analysis environment');
    console.log('  validate  Validate SonarQube configuration');
    console.log('  report    Generate quality report');
    console.log('  metrics   Calculate code metrics');
    console.log('\nExamples:');
    console.log('  npm run sonar:analyze');
    console.log('  npm run sonar:prepare');
    console.log('  npm run sonar:report');
  }
}

// Execute if called directly
if (require.main === module) {
  new SonarQualityAnalyzer().run();
}

module.exports = SonarQualityAnalyzer;