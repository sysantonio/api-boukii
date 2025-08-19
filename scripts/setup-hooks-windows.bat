@echo off
REM Boukii Admin V5 - Windows Git Hooks Setup
REM This script configures Git hooks for Windows environment

echo 🎣 Setting up Git hooks for Windows...

REM Configure Git to use the hooks directory
git config core.hooksPath .husky

REM Create .husky directory if it doesn't exist
if not exist ".husky" mkdir .husky

REM Copy hook files to .git/hooks if needed (Windows compatibility)
if exist ".git\hooks" (
    echo ✅ Git hooks directory exists
) else (
    echo ❌ Git hooks directory not found
    exit /b 1
)

echo ✅ Git hooks configured successfully!
echo 💡 To test: npm run hooks:validate