# V5TestDataSeeder - Professional Implementation

## Overview
Professional test data seeder for Boukii V5 Sprint T1.1.1, generating comprehensive Swiss ski school data for ESS Veveyse (School ID: 2).

## Features Generated

### 🏔️ Swiss Clients (50+)
- **55 realistic Swiss clients** with authentic names
- Swiss postal codes and cities (Vevey, Montreux, Fribourg, etc.)
- Authentic Swiss email domains (@bluewin.ch, @sunrise.ch, etc.)
- Swiss phone numbers (+41 format)
- Age distribution from 4 to 65 years
- Associated with School ID 2 via ClientsSchool relationship

### 🎿 Professional Monitors (8+)
- **10 specialized monitors** with expertise levels
- Ski, snowboard, and both specializations
- Experience levels: expert, advanced, intermediate
- Swiss personal data (AVS numbers, bank details)
- Associated with ESS Veveyse through MonitorsSchool

### 📚 Courses with CHF Pricing (15+)
- **15 comprehensive courses** (8 ski + 7 snowboard)
- Realistic Swiss pricing (CHF 75-180)
- Course duration and difficulty levels
- Weekend course dates for next 6 months
- Age-appropriate groupings (kids, teens, adults)

### 📅 Realistic Bookings (200+)
- **220 bookings** distributed over 6 months
- 30% cancellation insurance adoption
- 80% payment completion rate
- 90% attendance rate
- Mixed payment status for realistic dashboard data

### 💰 Financial Data
- Payment records for completed bookings
- Multiple payment methods (card, bank_transfer, cash, payrexx)
- CHF currency throughout
- Revenue tracking for dashboard analytics

### 🗓️ Season Management
- Current season: 2024-2025 (Dec 1 - Apr 30)
- Previous season: 2023-2024 (for comparison data)
- Active season configuration

## Usage

### Basic Execution
```bash
# Navigate to API directory
cd C:\laragon\www\api-boukii

# Run the seeder
php artisan db:seed --class=V5TestDataSeeder
```

### Validation (Optional)
```bash
# Run validation script first
php test_v5_seeder.php
```

## Generated Statistics
After execution, you'll see a summary like:
```
🎿 === V5 PROFESSIONAL DATA SUMMARY - T1.1.1 ===
🏫 School: ESS Veveyse (ID: 2)
⛷️  Current Season: 2024-2025
─────────────────────────────────────────────────
👥 Swiss Clients: 55
🎿 Professional Monitors: 10
📚 Courses (Ski/Snowboard): 15
📊 Bookings (6 months): 220
💰 Total Revenue: CHF XX,XXX.XX
📅 Seasons: 2
─────────────────────────────────────────────────
✅ T1.1.1 Sprint Requirements COMPLETED!
📈 Dashboard-ready with realistic Swiss data
🎯 Ready for V5 development and testing
```

## Sprint T1.1.1 Compliance

### ✅ Requirements Met
- [x] **50+ clientes suizos realistas** → 55 generated
- [x] **15+ cursos ski/snowboard con precios CHF** → 15 generated  
- [x] **200+ bookings distribuidos 6 meses** → 220 generated
- [x] **8+ monitors con especialidades** → 10 generated
- [x] **Financial data realista** → Complete payment system

### 🏗️ Database Structure
- Uses existing Boukii models and relationships
- Compatible with V5 multi-school/season architecture
- Maintains data integrity with foreign keys
- Follows existing naming conventions

### 🔒 Safety Features
- Transaction-wrapped execution (rollback on error)
- Uses `updateOrCreate` for safe re-execution
- Validates prerequisites before generation
- Comprehensive error handling

## Development Notes

### Key Models Used
- `App\V5\Models\Season` - V5 season management
- `App\Models\Client` - Client data with Swiss specifics
- `App\Models\Course` - Ski/snowboard courses
- `App\Models\Monitor` - Professional instructors
- `App\Models\Booking` - Reservation system
- `App\Models\Payment` - Financial transactions

### Data Relationships
- **Client ↔ School**: via `ClientsSchool` pivot
- **Monitor ↔ School**: via `MonitorsSchool` pivot  
- **Course → School**: direct `school_id` foreign key
- **Booking → Client**: via `client_main_id`
- **BookingUser**: links bookings to specific courses/dates

### Swiss Authenticity
- Real Swiss canton postal codes
- Authentic Swiss German/French names
- Realistic Swiss contact formats
- CHF pricing based on market rates
- Weekend-focused ski lesson schedule

## Next Steps
1. Execute the seeder in your Laravel environment
2. Verify data in V5 dashboard
3. Test multi-school/season context switching
4. Validate financial reporting accuracy
5. Use for V5 development and testing

## Support
- Seeder validates syntax automatically
- Comprehensive error messages
- Transaction safety for re-execution
- Compatible with existing Boukii V5 architecture