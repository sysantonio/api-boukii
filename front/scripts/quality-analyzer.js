#!/usr/bin/env node

const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

/**
 * Boukii Admin V5 - Quality Analyzer
 * Analyzes code quality metrics and generates reports
 */

class QualityAnalyzer {
  constructor() {
    this.projectRoot = path.join(__dirname, '..');
    this.srcPath = path.join(this.projectRoot, 'src');
    this.results = {
      timestamp: new Date().toISOString(),
      metrics: {},
      issues: [],
      recommendations: [],
    };
  }

  async analyze() {
    console.log('🔍 Starting Boukii Admin V5 Quality Analysis...\n');

    try {
      await this.analyzeFileStructure();
      await this.analyzeCodeComplexity();
      await this.analyzeDependencies();
      await this.analyzeTestCoverage();
      await this.generateReport();

      console.log('\n✅ Quality analysis completed successfully!');
      console.log(`📊 Report saved to: quality-report-${this.getDateString()}.json`);

      return this.results;
    } catch (error) {
      console.error('❌ Quality analysis failed:', error.message);
      process.exit(1);
    }
  }

  async analyzeFileStructure() {
    console.log('📁 Analyzing file structure...');

    const stats = this.getFileStats(this.srcPath);
    this.results.metrics.fileStructure = stats;

    // Check for architectural compliance
    this.checkArchitecturalCompliance();

    console.log(`   Found ${stats.totalFiles} files in ${stats.directories} directories`);
  }

  getFileStats(dir, stats = { totalFiles: 0, directories: 0, byExtension: {} }) {
    const items = fs.readdirSync(dir);

    for (const item of items) {
      const fullPath = path.join(dir, item);
      const stat = fs.statSync(fullPath);

      if (stat.isDirectory()) {
        stats.directories++;
        this.getFileStats(fullPath, stats);
      } else {
        stats.totalFiles++;
        const ext = path.extname(item);
        stats.byExtension[ext] = (stats.byExtension[ext] || 0) + 1;
      }
    }

    return stats;
  }

  checkArchitecturalCompliance() {
    const requiredDirs = [
      'src/app/core',
      'src/app/shared',
      'src/app/features',
      'src/app/ui',
      'src/app/state',
    ];

    const compliance = {
      hasRequiredStructure: true,
      missingDirectories: [],
    };

    for (const dir of requiredDirs) {
      const fullPath = path.join(this.projectRoot, dir);
      if (!fs.existsSync(fullPath)) {
        compliance.hasRequiredStructure = false;
        compliance.missingDirectories.push(dir);
      }
    }

    this.results.metrics.architecturalCompliance = compliance;

    if (!compliance.hasRequiredStructure) {
      this.results.issues.push({
        type: 'architecture',
        severity: 'high',
        message: `Missing required directories: ${compliance.missingDirectories.join(', ')}`,
      });
    }
  }

  async analyzeCodeComplexity() {
    console.log('🧮 Analyzing code complexity...');

    try {
      // Run ESLint with complexity reporting
      const eslintOutput = execSync('npx eslint src --format json', {
        encoding: 'utf8',
        cwd: this.projectRoot,
      });

      const eslintResults = JSON.parse(eslintOutput);
      const complexityIssues = this.extractComplexityIssues(eslintResults);

      this.results.metrics.complexity = {
        totalFiles: eslintResults.length,
        filesWithIssues: eslintResults.filter((f) => f.messages.length > 0).length,
        complexityViolations: complexityIssues.length,
        averageComplexity: this.calculateAverageComplexity(complexityIssues),
      };

      this.results.issues.push(...complexityIssues);

      console.log(`   Analyzed ${eslintResults.length} TypeScript files`);
      console.log(`   Found ${complexityIssues.length} complexity violations`);
    } catch (error) {
      console.log('   ⚠️  ESLint analysis failed (this is normal if there are errors)');
      this.results.metrics.complexity = { error: error.message };
    }
  }

  extractComplexityIssues(eslintResults) {
    const complexityIssues = [];

    for (const fileResult of eslintResults) {
      for (const message of fileResult.messages) {
        if (
          message.ruleId === 'complexity' ||
          message.ruleId === 'max-lines-per-function' ||
          message.ruleId === 'max-lines'
        ) {
          complexityIssues.push({
            type: 'complexity',
            severity: message.severity === 2 ? 'error' : 'warning',
            file: fileResult.filePath,
            line: message.line,
            rule: message.ruleId,
            message: message.message,
          });
        }
      }
    }

    return complexityIssues;
  }

  calculateAverageComplexity(issues) {
    const complexityValues = issues
      .filter((issue) => issue.rule === 'complexity')
      .map((issue) => {
        const match = issue.message.match(/complexity of (\d+)/);
        return match ? parseInt(match[1]) : 0;
      })
      .filter((val) => val > 0);

    return complexityValues.length > 0
      ? complexityValues.reduce((a, b) => a + b, 0) / complexityValues.length
      : 0;
  }

  async analyzeDependencies() {
    console.log('📦 Analyzing dependencies...');

    try {
      const packageJson = JSON.parse(
        fs.readFileSync(path.join(this.projectRoot, 'package.json'), 'utf8')
      );

      const deps = Object.keys(packageJson.dependencies || {});
      const devDeps = Object.keys(packageJson.devDependencies || {});

      this.results.metrics.dependencies = {
        production: deps.length,
        development: devDeps.length,
        total: deps.length + devDeps.length,
        angular: deps.filter((dep) => dep.includes('@angular')).length,
        testing: devDeps.filter(
          (dep) => dep.includes('test') || dep.includes('jest') || dep.includes('karma')
        ).length,
      };

      // Check for outdated dependencies
      try {
        const ncuOutput = execSync('npx npm-check-updates --format json', {
          encoding: 'utf8',
          cwd: this.projectRoot,
        });
        const outdated = JSON.parse(ncuOutput);
        this.results.metrics.dependencies.outdated = Object.keys(outdated).length;

        if (Object.keys(outdated).length > 0) {
          this.results.recommendations.push({
            type: 'dependencies',
            message: `${Object.keys(outdated).length} dependencies can be updated`,
            action: 'Run: npm run analyze:deps',
          });
        }
      } catch (error) {
        console.log('   ⚠️  Could not check for outdated dependencies');
      }

      console.log(`   ${deps.length} production dependencies`);
      console.log(`   ${devDeps.length} development dependencies`);
    } catch (error) {
      console.log('   ❌ Dependency analysis failed:', error.message);
    }
  }

  async analyzeTestCoverage() {
    console.log('🧪 Analyzing test coverage...');

    try {
      // Check if coverage directory exists
      const coveragePath = path.join(this.projectRoot, 'coverage');
      if (!fs.existsSync(coveragePath)) {
        console.log('   ⚠️  No coverage data found. Run: npm run test:ci');
        this.results.metrics.coverage = { available: false };
        return;
      }

      // Try to read coverage summary
      const coverageSummaryPath = path.join(coveragePath, 'coverage-summary.json');
      if (fs.existsSync(coverageSummaryPath)) {
        const coverageSummary = JSON.parse(fs.readFileSync(coverageSummaryPath, 'utf8'));

        this.results.metrics.coverage = {
          available: true,
          lines: coverageSummary.total.lines.pct,
          statements: coverageSummary.total.statements.pct,
          functions: coverageSummary.total.functions.pct,
          branches: coverageSummary.total.branches.pct,
        };

        console.log(`   Coverage: ${coverageSummary.total.lines.pct}% lines`);

        // Check coverage thresholds
        if (coverageSummary.total.lines.pct < 80) {
          this.results.issues.push({
            type: 'coverage',
            severity: 'warning',
            message: `Line coverage (${coverageSummary.total.lines.pct}%) is below 80% threshold`,
          });
        }
      }
    } catch (error) {
      console.log('   ❌ Coverage analysis failed:', error.message);
      this.results.metrics.coverage = { error: error.message };
    }
  }

  async generateReport() {
    console.log('📊 Generating quality report...');

    // Calculate overall quality score
    this.results.qualityScore = this.calculateQualityScore();

    // Add summary
    this.results.summary = {
      totalIssues: this.results.issues.length,
      criticalIssues: this.results.issues.filter((i) => i.severity === 'error').length,
      warningIssues: this.results.issues.filter((i) => i.severity === 'warning').length,
      recommendations: this.results.recommendations.length,
      qualityGrade: this.getQualityGrade(this.results.qualityScore),
    };

    // Save report
    const reportPath = path.join(this.projectRoot, `quality-report-${this.getDateString()}.json`);

    fs.writeFileSync(reportPath, JSON.stringify(this.results, null, 2));

    // Generate console summary
    this.printSummary();
  }

  calculateQualityScore() {
    let score = 100;

    // Deduct points for issues
    score -= this.results.issues.filter((i) => i.severity === 'error').length * 5;
    score -= this.results.issues.filter((i) => i.severity === 'warning').length * 2;

    // Bonus for good coverage
    if (this.results.metrics.coverage && this.results.metrics.coverage.lines) {
      if (this.results.metrics.coverage.lines >= 90) score += 5;
      else if (this.results.metrics.coverage.lines >= 80) score += 2;
    }

    // Bonus for architectural compliance
    if (this.results.metrics.architecturalCompliance?.hasRequiredStructure) {
      score += 5;
    }

    return Math.max(0, Math.min(100, score));
  }

  getQualityGrade(score) {
    if (score >= 90) return 'A';
    if (score >= 80) return 'B';
    if (score >= 70) return 'C';
    if (score >= 60) return 'D';
    return 'F';
  }

  printSummary() {
    const { summary } = this.results;

    console.log('\n' + '='.repeat(50));
    console.log('📊 BOUKII ADMIN V5 - QUALITY REPORT SUMMARY');
    console.log('='.repeat(50));

    console.log(
      `🎯 Quality Score: ${this.results.qualityScore}/100 (Grade: ${summary.qualityGrade})`
    );
    console.log(`📁 Files Analyzed: ${this.results.metrics.fileStructure?.totalFiles || 'N/A'}`);

    if (this.results.metrics.coverage?.available) {
      console.log(`🧪 Test Coverage: ${this.results.metrics.coverage.lines}%`);
    }

    console.log(`📦 Dependencies: ${this.results.metrics.dependencies?.total || 'N/A'}`);
    console.log(
      `⚠️  Issues Found: ${summary.totalIssues} (${summary.criticalIssues} critical, ${summary.warningIssues} warnings)`
    );
    console.log(`💡 Recommendations: ${summary.recommendations}`);

    if (summary.qualityGrade === 'A') {
      console.log('\n🎉 Excellent code quality! Keep up the great work!');
    } else if (summary.qualityGrade === 'B') {
      console.log('\n👍 Good code quality with room for improvement.');
    } else {
      console.log('\n🔧 Code quality needs attention. Review the issues and recommendations.');
    }

    console.log('='.repeat(50));
  }

  getDateString() {
    return new Date().toISOString().split('T')[0];
  }
}

// Run analyzer if called directly
if (require.main === module) {
  const analyzer = new QualityAnalyzer();
  analyzer.analyze().catch(console.error);
}

module.exports = QualityAnalyzer;
