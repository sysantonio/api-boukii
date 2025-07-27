# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel API application for a sports course booking and management system (specifically designed for ski schools and similar sports instruction businesses). The system handles course bookings, client management, instructor scheduling, payments, and comprehensive financial analytics.

## Common Development Commands

### PHP/Laravel Commands
```bash
# Install dependencies
composer install

# Run database migrations
php artisan migrate

# Generate application key
php artisan key:generate

# Clear application cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Run background jobs
php artisan queue:work

# Generate API documentation
php artisan l5-swagger:generate
```

### Node.js Commands
```bash
# Install frontend dependencies
npm install

# Development server
npm run dev

# Build for production
npm run build
```

### Testing
```bash
# Run all tests
vendor/bin/phpunit

# Run specific test suite
vendor/bin/phpunit tests/Feature
vendor/bin/phpunit tests/Unit

# Run tests with coverage
vendor/bin/phpunit --coverage-html coverage
```

### Code Quality
```bash
# Run Laravel Pint (code formatting)
vendor/bin/pint

# Check code style
vendor/bin/pint --test
```

## Architecture Overview

### Domain Models
- **Booking**: Central entity managing course reservations with complex pricing, payments, and voucher systems
- **Course**: Sports courses with flexible pricing models (collective/private/activities) organized in groups and subgroups  
- **Client**: Customer management with multi-language support and utilizer relationships
- **Monitor**: Instructors with sport-specific degrees and availability management
- **School**: Multi-tenant architecture where each school manages its own courses and settings
- **User**: Authentication with role-based permissions (admin/client/monitor/superadmin)

### Key Services
- **BookingPriceCalculatorService** (`app/Http/Services/BookingPriceCalculatorService.php`): Sophisticated pricing engine handling flexible vs fixed pricing, multi-participant calculations, extras, insurance, and discount logic
- **AnalyticsService** (`app/Http/Services/AnalyticsService.php`): Financial reporting and dashboard generation
- **PayrexxService** (`app/Services/Payrexx/PayrexxService.php`): Payment gateway integration
- **SeasonFinanceService** (`app/Services/Finance/SeasonFinanceService.php`): Seasonal financial analytics

### Repository Pattern
All models use the Repository pattern with a `BaseRepository` providing common CRUD operations. Repositories handle:
- Multi-tenant filtering (school-based data isolation)
- Standardized search and pagination
- Soft delete handling

### API Organization
Routes are organized by role and functionality:
- `/api/admin/` - Admin panel with comprehensive management features
- `/api/teach/` - Monitor/instructor mobile app endpoints  
- `/api/sports/` - Client sports app endpoints
- `/api/slug/` - Booking iframe functionality
- `/api/` - Public API endpoints

Authentication uses Laravel Sanctum with ability-based permissions.

## Development Patterns

### Multi-tenancy
The system implements school-based multi-tenancy. Most queries automatically filter by `school_id`. When working with data:
- Always consider school context
- Use school-scoped relationships where appropriate
- Check existing patterns in repositories for school filtering

### Financial Calculations
The `Utils` trait (`app/Traits/Utils.php`) contains complex business calculations:
- Course availability and duration calculations
- Multi-type pricing logic (collective, private, activity courses)
- Monitor availability management
- Use existing methods rather than reimplementing calculations

### Pricing System
Courses support flexible pricing models:
- **Fixed pricing**: Set price regardless of participants
- **Flexible pricing**: Price varies by participant count with min/max constraints
- **Extras**: Insurance, equipment, additional services
- **Vouchers**: Discount codes and prepaid vouchers
- Complex logic is handled in `BookingPriceCalculatorService`

### Localization
The system supports multiple languages (en, fr, de, es, it):
- Email templates are localized per school
- Client communications use their preferred language
- Translation files are in `resources/lang/`

## Testing Strategy

The application has comprehensive test coverage:
- **API Tests**: `tests/APIs/` - Test all API endpoints
- **Repository Tests**: `tests/Repositories/` - Test data access layer
- **Unit Tests**: `tests/Unit/` - Test individual components
- **Feature Tests**: `tests/Feature/` - Test application workflows

When adding new features:
1. Write API tests for new endpoints
2. Add repository tests for new data access methods
3. Include unit tests for complex business logic

## Database Considerations

### Key Relationships
- Many-to-many: Clients ↔ Schools, Clients ↔ Sports, Monitors ↔ Schools
- Complex hierarchies: Course → CourseGroup → CourseSubgroup → BookingUsers
- Financial tracking: Booking → Payments, Booking → VoucherLogs

### Migration Strategy
- Migrations include both current and legacy (`database/migrations/old/`) structures
- Use `doctrine/dbal` for complex schema modifications
- Always include rollback functionality

## Performance Considerations

### Caching
- Financial dashboards use cache keys generated by `FinanceCacheKeyTrait`
- Heavy calculations in analytics are cached
- Clear relevant caches when updating related data

### Database Optimization
- Indexes exist for common query patterns (see recent migrations)
- Use eager loading for relationships to avoid N+1 queries
- Repository pattern includes optimized queries for common operations

## External Integrations

### Payrexx Payment Gateway
- Service class: `PayrexxService`
- Configuration in `config/services.php`
- Webhook handling in payment controllers

### Weather API (AccuWeather)
- Integration for station-based weather forecasts
- Helper utilities in `AccuweatherHelpers`
- Scheduled command: `StationWeatherForecast`

## Email System

### Mail Classes
Email templates are stored in database and managed per school:
- Use `Mail` model for template management
- Localization handled automatically based on client language
- Preview functionality available in admin panel

### Common Mail Types
- Booking confirmations/cancellations
- Payment notifications
- Course information updates
- Password reset functionality

## Background Jobs

### Scheduled Commands
- `BookingCancel`: Process automatic cancellations
- `BookingCancelUnpaid`: Clean up unpaid bookings
- `MonitorDashboardPerformance`: Update performance metrics
- Configure in `app/Console/Kernel.php`

### Queue Jobs
- Email sending
- Heavy calculations
- External API calls
- Configure queue driver in `.env`

## Development Workflow

1. **Database Setup**: Run migrations and seeders for local development
2. **API Documentation**: Generate Swagger docs after API changes
3. **Testing**: Run test suite before committing changes
4. **Code Style**: Use Laravel Pint for consistent formatting
5. **Caching**: Clear relevant caches after configuration changes
