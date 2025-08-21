#!/bin/bash

# V5 Migration Helper Script
# This script helps manage the organized migration structure

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

function print_header() {
    echo -e "${BLUE}================================${NC}"
    echo -e "${BLUE}  V5 Migration Helper Script    ${NC}"
    echo -e "${BLUE}================================${NC}"
    echo
}

function print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

function print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

function print_error() {
    echo -e "${RED}❌ $1${NC}"
}

function print_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

function show_help() {
    echo "Usage: $0 [command]"
    echo
    echo "Commands:"
    echo "  old      - Run only 'old' migrations (base structure)"
    echo "  v5       - Run only V5 migrations (root directory)"  
    echo "  all      - Run old migrations, then V5 migrations"
    echo "  status   - Show migration status"
    echo "  rollback - Rollback last batch of migrations"
    echo "  fresh    - Fresh migrate with seed (DESTRUCTIVE)"
    echo "  help     - Show this help message"
    echo
    echo "Examples:"
    echo "  $0 old           # Run base structure migrations"
    echo "  $0 v5            # Run V5-specific migrations"
    echo "  $0 all           # Run everything in correct order"
    echo "  $0 status        # Check which migrations have run"
}

function run_old_migrations() {
    print_info "Running OLD migrations (base structure)..."
    if php artisan migrate --path=database/migrations/old --verbose; then
        print_success "OLD migrations completed successfully"
    else
        print_error "OLD migrations failed"
        exit 1
    fi
}

function run_v5_migrations() {
    print_info "Running V5 migrations (new functionality)..."
    if php artisan migrate --path=database/migrations --verbose; then
        print_success "V5 migrations completed successfully"  
    else
        print_error "V5 migrations failed"
        exit 1
    fi
}

function show_status() {
    print_info "Migration status:"
    php artisan migrate:status
}

function run_all_migrations() {
    print_info "Running complete migration sequence..."
    run_old_migrations
    echo
    run_v5_migrations
    echo
    print_success "All migrations completed successfully!"
}

function rollback_migrations() {
    print_warning "Rolling back last batch of migrations..."
    read -p "Are you sure? This will rollback the last batch. (y/N): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        php artisan migrate:rollback --verbose
        print_success "Rollback completed"
    else
        print_info "Rollback cancelled"
    fi
}

function fresh_migrate() {
    print_error "DESTRUCTIVE OPERATION: This will drop all tables!"
    read -p "Are you absolutely sure? Type 'YES' to continue: " confirmation
    if [ "$confirmation" = "YES" ]; then
        print_info "Running fresh migration..."
        php artisan migrate:fresh --verbose
        run_old_migrations
        echo
        run_v5_migrations
        echo
        print_success "Fresh migration completed!"
    else
        print_info "Fresh migration cancelled"
    fi
}

# Main script logic
print_header

case ${1:-help} in
    old)
        run_old_migrations
        ;;
    v5)
        run_v5_migrations
        ;;
    all)
        run_all_migrations
        ;;
    status)
        show_status
        ;;
    rollback)
        rollback_migrations
        ;;
    fresh)
        fresh_migrate
        ;;
    help|--help|-h)
        show_help
        ;;
    *)
        print_error "Unknown command: $1"
        echo
        show_help
        exit 1
        ;;
esac