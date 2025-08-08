# Column Not Found Fix - schools.domain & schools.logo_url

## Problem Description

After fixing the SQL ambiguity error, a new error appeared:

```sql
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'schools.domain' in 'field list'
```

This occurred because the controllers were trying to select columns that don't exist in the actual database schema.

## Root Cause Analysis

The code was referencing non-existent columns:
- ❌ `schools.domain` - This column doesn't exist in the schools table
- ❌ `schools.logo_url` - This column doesn't exist (actual column is `schools.logo`)

Additionally, there were duplicate filters being applied:
- The `User::schools()` relationship already filters by `schools.active = 1`
- Controllers were adding the same filter again, causing duplication

## Database Schema Verification

**Actual columns in `schools` table:**
```php
// From app/Models/School.php fillable array:
'id', 'name', 'description', 'contact_email', 'contact_phone', 'contact_telephone',
'contact_address', 'contact_cp', 'contact_city', 'contact_province', 'contact_country',
'fiscal_name', 'fiscal_id', 'fiscal_address', 'fiscal_cp', 'fiscal_city',
'fiscal_province', 'fiscal_country', 'iban', 'logo', 'slug', 'cancellation_insurance_percent',
'payrexx_instance', 'payrexx_key', 'conditions_url', 'bookings_comission_cash',
'bookings_comission_boukii_pay', 'bookings_comission_other', 'school_rate', 'has_ski',
'has_snowboard', 'has_telemark', 'has_rando', 'inscription', 'type', 'active', 'settings',
'current_season_id'
```

## Solution Implemented

### 1. Fixed Column References

**File**: `C:\laragon\www\api-boukii\app\Http\Controllers\API\V5\Auth\AuthController.php` (line 65-66)

```php
// ❌ BEFORE (non-existent columns):
->select(['schools.id', 'schools.name', 'schools.slug', 'schools.domain', 'schools.logo_url'])

// ✅ AFTER (actual columns):
->select(['schools.id', 'schools.name', 'schools.slug', 'schools.logo'])
```

**Updated response mapping:**
```php
// ❌ BEFORE:
return [
    'id' => $school->id,
    'name' => $school->name,
    'slug' => $school->slug,
    'domain' => $school->domain,        // ❌ Non-existent
    'logo_url' => $school->logo_url,    // ❌ Non-existent
];

// ✅ AFTER:
return [
    'id' => $school->id,
    'name' => $school->name,
    'slug' => $school->slug,
    'logo' => $school->logo,            // ✅ Actual column
];
```

### 2. Removed Duplicate Filters

**File**: `C:\laragon\www\api-boukii\app\Http\Controllers\API\V5\Auth\AuthController.php`

```php
// ❌ BEFORE (duplicate filter):
$schools = $user->schools()
    ->where('active', 1)  // ❌ Duplicate - already in relationship
    ->select([...])

// ✅ AFTER (no duplicate):
$schools = $user->schools()
    ->select([...])       // ✅ Clean, no duplication
```

**File**: `C:\laragon\www\api-boukii\app\Http\Controllers\API\V5\AuthV5Controller.php`

```php
// ❌ BEFORE:
$schools = $user->schools()
    ->where('schools.active', true)  // ❌ Duplicate
    ->get();

// ✅ AFTER:
$schools = $user->schools()->get(); // ✅ Clean
```

### 3. Updated User Model Scope

**File**: `C:\laragon\www\api-boukii\app\Models\User.php` (lines 240-244)

```php
public function scopeWithSafeSchools($query)
{
    return $query->with(['schools' => function ($schoolQuery) {
        $schoolQuery->select([
            'schools.id',
            'schools.name',
            'schools.slug', 
            'schools.logo'    // ✅ Using actual column
        ]);
    }]);
}
```

### 4. Updated Tests

**File**: `C:\laragon\www\api-boukii\tests\Unit\UserSchoolsRelationshipTest.php`

- Updated factory calls to use `'logo'` instead of `'logo_url'`
- Updated select statements to use actual columns
- Removed references to non-existent `'domain'` field

## Files Modified

1. **Controllers**:
   - `C:\laragon\www\api-boukii\app\Http\Controllers\API\V5\Auth\AuthController.php`
   - `C:\laragon\www\api-boukii\app\Http\Controllers\API\V5\AuthV5Controller.php`

2. **Models**:
   - `C:\laragon\www\api-boukii\app\Models\User.php`

3. **Tests**:
   - `C:\laragon\www\api-boukii\tests\Unit\UserSchoolsRelationshipTest.php`

4. **Documentation**:
   - `C:\laragon\www\api-boukii\docs\COLUMN_NOT_FOUND_FIX.md`

## Expected Results

✅ **Column Errors Resolved**: No more `Unknown column 'schools.domain'` or `'schools.logo_url'` errors  
✅ **Clean Queries**: No duplicate filters in SQL queries  
✅ **Proper Data Return**: API responses use actual database columns  
✅ **Performance**: Cleaner, more efficient queries  
✅ **Consistency**: All references use the same column names  

## API Response Changes

**Before:**
```json
{
  "id": 1,
  "name": "School Name",
  "slug": "school-name",
  "domain": null,     // ❌ Non-existent field
  "logo_url": null    // ❌ Non-existent field
}
```

**After:**
```json
{
  "id": 1,
  "name": "School Name", 
  "slug": "school-name",
  "logo": "path/to/logo.png"  // ✅ Actual database field
}
```

## Testing

To verify the fix works:

```bash
# Test the specific relationship
cd C:\laragon\www\api-boukii
php artisan test tests/Unit/UserSchoolsRelationshipTest.php

# Or test authentication endpoints directly
# Make a POST request to /api/v5/auth/check-user
```

The `Column not found` error should now be completely resolved.