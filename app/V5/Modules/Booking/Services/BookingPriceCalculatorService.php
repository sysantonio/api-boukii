<?php

namespace App\V5\Modules\Booking\Services;

use App\V5\Logging\V5Logger;
use App\V5\Exceptions\PriceCalculationException;
use Carbon\Carbon;

/**
 * V5 Booking Price Calculator Service
 * 
 * Handles complex pricing calculations for bookings including:
 * - Base course/activity prices
 * - Dynamic pricing based on demand/season
 * - Equipment rental fees
 * - Insurance costs
 * - Discounts and promotions
 * - Tax calculations
 */
class BookingPriceCalculatorService
{
    private const DEFAULT_CURRENCY = 'EUR';
    private const TAX_RATE = 0.21; // 21% VAT
    private const INSURANCE_RATE = 0.05; // 5% of total price
    private const EQUIPMENT_DAILY_RATE = 15.00; // Default daily rate per item

    /**
     * Calculate total booking price with all components
     */
    public function calculateBookingPrice(array $bookingData, int $seasonId, int $schoolId): array
    {
        V5Logger::logPerformance('price_calculation_started', 0, [
            'season_id' => $seasonId,
            'school_id' => $schoolId,
            'type' => $bookingData['type'] ?? null,
        ]);

        $startTime = microtime(true);

        try {
            // Initialize price structure
            $priceData = [
                'base_price' => 0.00,
                'extras_price' => 0.00,
                'equipment_price' => 0.00,
                'insurance_price' => 0.00,
                'tax_amount' => 0.00,
                'discount_amount' => 0.00,
                'total_price' => 0.00,
                'currency' => self::DEFAULT_CURRENCY,
                'breakdown' => [],
            ];

            // Calculate base price
            $priceData['base_price'] = $this->calculateBasePrice($bookingData, $seasonId, $schoolId);
            $priceData['breakdown']['base_price'] = $priceData['base_price'];

            // Calculate extras price
            if (!empty($bookingData['extras'])) {
                $priceData['extras_price'] = $this->calculateExtrasPrice($bookingData['extras']);
                $priceData['breakdown']['extras'] = $this->getExtrasBreakdown($bookingData['extras']);
            }

            // Calculate equipment price
            if (!empty($bookingData['equipment'])) {
                $priceData['equipment_price'] = $this->calculateEquipmentPrice(
                    $bookingData['equipment'], 
                    $bookingData
                );
                $priceData['breakdown']['equipment'] = $this->getEquipmentBreakdown($bookingData['equipment']);
            }

            // Calculate insurance price
            if (!empty($bookingData['has_insurance']) || !empty($bookingData['insurance'])) {
                $subtotal = $priceData['base_price'] + $priceData['extras_price'] + $priceData['equipment_price'];
                $priceData['insurance_price'] = $this->calculateInsurancePrice($subtotal, $bookingData);
                $priceData['breakdown']['insurance'] = $priceData['insurance_price'];
            }

            // Calculate discounts
            $subtotalBeforeDiscount = $priceData['base_price'] + $priceData['extras_price'] + 
                                    $priceData['equipment_price'] + $priceData['insurance_price'];
            
            $discountData = $this->calculateDiscounts($bookingData, $subtotalBeforeDiscount, $seasonId, $schoolId);
            $priceData['discount_amount'] = $discountData['total_discount'];
            $priceData['breakdown']['discounts'] = $discountData['breakdown'];

            // Calculate subtotal after discount
            $subtotalAfterDiscount = $subtotalBeforeDiscount - $priceData['discount_amount'];

            // Calculate tax
            $priceData['tax_amount'] = $this->calculateTax($subtotalAfterDiscount, $schoolId);
            $priceData['breakdown']['tax'] = $priceData['tax_amount'];

            // Calculate total
            $priceData['total_price'] = $subtotalAfterDiscount + $priceData['tax_amount'];

            // Apply dynamic pricing adjustments
            $dynamicAdjustment = $this->calculateDynamicPricing($bookingData, $priceData['total_price'], $seasonId);
            if ($dynamicAdjustment['adjustment'] != 0) {
                $priceData['total_price'] += $dynamicAdjustment['adjustment'];
                $priceData['breakdown']['dynamic_pricing'] = $dynamicAdjustment;
            }

            // Round final price
            $priceData['total_price'] = round($priceData['total_price'], 2);

            $duration = microtime(true) - $startTime;
            V5Logger::logPerformance('price_calculation_completed', $duration * 1000, [
                'total_price' => $priceData['total_price'],
                'components' => array_keys($priceData['breakdown']),
            ]);

            return $priceData;

        } catch (\Exception $e) {
            V5Logger::logSystemError($e, [
                'operation' => 'price_calculation',
                'booking_data' => $bookingData,
            ]);
            throw new PriceCalculationException('Failed to calculate booking price: ' . $e->getMessage());
        }
    }

    /**
     * Calculate base price for course/activity
     */
    private function calculateBasePrice(array $bookingData, int $seasonId, int $schoolId): float
    {
        $basePrice = 0.00;

        switch ($bookingData['type']) {
            case 'course':
                $basePrice = $this->calculateCoursePrice($bookingData, $seasonId, $schoolId);
                break;
            case 'activity':
                $basePrice = $this->calculateActivityPrice($bookingData, $seasonId, $schoolId);
                break;
            case 'material':
                $basePrice = $this->calculateMaterialPrice($bookingData, $seasonId, $schoolId);
                break;
            default:
                throw new PriceCalculationException('Invalid booking type for price calculation');
        }

        return round($basePrice, 2);
    }

    /**
     * Calculate course price
     */
    private function calculateCoursePrice(array $bookingData, int $seasonId, int $schoolId): float
    {
        // This would typically query the Course model to get base price
        // For now, we'll use a basic calculation
        
        $basePricePerPerson = 50.00; // Default course price
        $participants = count($bookingData['participants'] ?? [1]);
        
        // Apply group size adjustments
        $groupAdjustment = $this->calculateGroupSizeAdjustment($participants);
        
        return ($basePricePerPerson * $participants) * (1 + $groupAdjustment);
    }

    /**
     * Calculate activity price
     */
    private function calculateActivityPrice(array $bookingData, int $seasonId, int $schoolId): float
    {
        $basePricePerPerson = 30.00; // Default activity price
        $participants = count($bookingData['participants'] ?? [1]);
        
        // Calculate duration multiplier
        $durationMultiplier = $this->calculateDurationMultiplier($bookingData);
        
        return ($basePricePerPerson * $participants) * $durationMultiplier;
    }

    /**
     * Calculate material/equipment rental price
     */
    private function calculateMaterialPrice(array $bookingData, int $seasonId, int $schoolId): float
    {
        $basePrice = 20.00; // Base material rental price
        $days = $this->calculateRentalDays($bookingData);
        
        return $basePrice * $days;
    }

    /**
     * Calculate extras price
     */
    private function calculateExtrasPrice(array $extras): float
    {
        $totalPrice = 0.00;
        
        foreach ($extras as $extra) {
            $unitPrice = $extra['unit_price'] ?? 0.00;
            $quantity = $extra['quantity'] ?? 1;
            $totalPrice += $unitPrice * $quantity;
        }
        
        return round($totalPrice, 2);
    }

    /**
     * Calculate equipment rental price
     */
    private function calculateEquipmentPrice(array $equipment, array $bookingData): float
    {
        $totalPrice = 0.00;
        $rentalDays = $this->calculateRentalDays($bookingData);
        
        foreach ($equipment as $item) {
            $dailyRate = $item['daily_rate'] ?? self::EQUIPMENT_DAILY_RATE;
            $totalPrice += $dailyRate * $rentalDays;
        }
        
        return round($totalPrice, 2);
    }

    /**
     * Calculate insurance price
     */
    private function calculateInsurancePrice(float $subtotal, array $bookingData): float
    {
        if (isset($bookingData['insurance']['fixed_price'])) {
            return $bookingData['insurance']['fixed_price'];
        }
        
        // Calculate as percentage of subtotal
        $rate = $bookingData['insurance']['rate'] ?? self::INSURANCE_RATE;
        return round($subtotal * $rate, 2);
    }

    /**
     * Calculate discounts
     */
    private function calculateDiscounts(array $bookingData, float $subtotal, int $seasonId, int $schoolId): array
    {
        $totalDiscount = 0.00;
        $breakdown = [];

        // Early bird discount
        $earlyBirdDiscount = $this->calculateEarlyBirdDiscount($bookingData, $subtotal);
        if ($earlyBirdDiscount > 0) {
            $totalDiscount += $earlyBirdDiscount;
            $breakdown['early_bird'] = $earlyBirdDiscount;
        }

        // Group discount
        $groupDiscount = $this->calculateGroupDiscount($bookingData, $subtotal);
        if ($groupDiscount > 0) {
            $totalDiscount += $groupDiscount;
            $breakdown['group'] = $groupDiscount;
        }

        // Loyalty discount
        $loyaltyDiscount = $this->calculateLoyaltyDiscount($bookingData, $subtotal);
        if ($loyaltyDiscount > 0) {
            $totalDiscount += $loyaltyDiscount;
            $breakdown['loyalty'] = $loyaltyDiscount;
        }

        // Promotional codes
        $promoDiscount = $this->calculatePromoDiscount($bookingData, $subtotal);
        if ($promoDiscount > 0) {
            $totalDiscount += $promoDiscount;
            $breakdown['promo_code'] = $promoDiscount;
        }

        return [
            'total_discount' => round($totalDiscount, 2),
            'breakdown' => $breakdown,
        ];
    }

    /**
     * Calculate tax amount
     */
    private function calculateTax(float $subtotal, int $schoolId): float
    {
        // Tax rate could vary by school/location
        $taxRate = self::TAX_RATE;
        
        return round($subtotal * $taxRate, 2);
    }

    /**
     * Calculate dynamic pricing adjustments
     */
    private function calculateDynamicPricing(array $bookingData, float $baseTotal, int $seasonId): array
    {
        $adjustment = 0.00;
        $factors = [];

        // Demand-based pricing
        $demandMultiplier = $this->calculateDemandMultiplier($bookingData, $seasonId);
        if ($demandMultiplier != 1.0) {
            $demandAdjustment = $baseTotal * ($demandMultiplier - 1.0);
            $adjustment += $demandAdjustment;
            $factors['demand'] = [
                'multiplier' => $demandMultiplier,
                'adjustment' => $demandAdjustment,
            ];
        }

        // Seasonal pricing
        $seasonalMultiplier = $this->calculateSeasonalMultiplier($bookingData);
        if ($seasonalMultiplier != 1.0) {
            $seasonalAdjustment = $baseTotal * ($seasonalMultiplier - 1.0);
            $adjustment += $seasonalAdjustment;
            $factors['seasonal'] = [
                'multiplier' => $seasonalMultiplier,
                'adjustment' => $seasonalAdjustment,
            ];
        }

        // Last-minute pricing
        $lastMinuteMultiplier = $this->calculateLastMinuteMultiplier($bookingData);
        if ($lastMinuteMultiplier != 1.0) {
            $lastMinuteAdjustment = $baseTotal * ($lastMinuteMultiplier - 1.0);
            $adjustment += $lastMinuteAdjustment;
            $factors['last_minute'] = [
                'multiplier' => $lastMinuteMultiplier,
                'adjustment' => $lastMinuteAdjustment,
            ];
        }

        return [
            'adjustment' => round($adjustment, 2),
            'factors' => $factors,
        ];
    }

    /**
     * Helper methods for price calculations
     */
    private function calculateGroupSizeAdjustment(int $participants): float
    {
        if ($participants >= 10) return -0.15; // 15% discount for 10+ people
        if ($participants >= 5) return -0.10;  // 10% discount for 5+ people
        if ($participants >= 3) return -0.05;  // 5% discount for 3+ people
        return 0.00;
    }

    private function calculateDurationMultiplier(array $bookingData): float
    {
        $startDate = Carbon::parse($bookingData['start_date'] ?? now());
        $endDate = Carbon::parse($bookingData['end_date'] ?? $startDate);
        
        $days = max(1, $startDate->diffInDays($endDate) + 1);
        
        // Progressive discount for longer bookings
        if ($days >= 7) return 0.85; // 15% discount for weekly bookings
        if ($days >= 3) return 0.95; // 5% discount for 3+ day bookings
        
        return 1.0;
    }

    private function calculateRentalDays(array $bookingData): int
    {
        $startDate = Carbon::parse($bookingData['start_date'] ?? now());
        $endDate = Carbon::parse($bookingData['end_date'] ?? $startDate);
        
        return max(1, $startDate->diffInDays($endDate) + 1);
    }

    private function calculateEarlyBirdDiscount(array $bookingData, float $subtotal): float
    {
        $startDate = Carbon::parse($bookingData['start_date'] ?? now());
        $daysUntilStart = now()->diffInDays($startDate, false);
        
        if ($daysUntilStart >= 30) {
            return $subtotal * 0.10; // 10% discount for 30+ days advance
        } elseif ($daysUntilStart >= 14) {
            return $subtotal * 0.05; // 5% discount for 14+ days advance
        }
        
        return 0.00;
    }

    private function calculateGroupDiscount(array $bookingData, float $subtotal): float
    {
        $participants = count($bookingData['participants'] ?? []);
        
        if ($participants >= 15) {
            return $subtotal * 0.20; // 20% discount for 15+ people
        } elseif ($participants >= 10) {
            return $subtotal * 0.15; // 15% discount for 10+ people
        } elseif ($participants >= 5) {
            return $subtotal * 0.10; // 10% discount for 5+ people
        }
        
        return 0.00;
    }

    private function calculateLoyaltyDiscount(array $bookingData, float $subtotal): float
    {
        // This would typically check client's booking history
        // For now, return 0 as we'd need to query the database
        return 0.00;
    }

    private function calculatePromoDiscount(array $bookingData, float $subtotal): float
    {
        if (empty($bookingData['promo_code'])) {
            return 0.00;
        }
        
        // This would typically validate promo code against database
        // For now, simulate some basic promo codes
        $promoCode = strtoupper($bookingData['promo_code']);
        
        switch ($promoCode) {
            case 'WELCOME10':
                return $subtotal * 0.10;
            case 'SAVE20':
                return min($subtotal * 0.20, 50.00); // Max €50 discount
            case 'FIRST15':
                return $subtotal * 0.15;
            default:
                return 0.00;
        }
    }

    private function calculateDemandMultiplier(array $bookingData, int $seasonId): float
    {
        // This would typically check booking density for the requested dates
        // For now, return base multiplier
        return 1.0;
    }

    private function calculateSeasonalMultiplier(array $bookingData): float
    {
        $startDate = Carbon::parse($bookingData['start_date'] ?? now());
        $month = $startDate->month;
        
        // Peak season pricing (winter months for ski activities)
        if (in_array($month, [12, 1, 2, 3])) {
            return 1.25; // 25% premium for peak season
        }
        
        // Shoulder season
        if (in_array($month, [11, 4])) {
            return 1.10; // 10% premium for shoulder season
        }
        
        // Off-season discount
        return 0.85; // 15% discount for off-season
    }

    private function calculateLastMinuteMultiplier(array $bookingData): float
    {
        $startDate = Carbon::parse($bookingData['start_date'] ?? now());
        $hoursUntilStart = now()->diffInHours($startDate, false);
        
        if ($hoursUntilStart <= 24) {
            return 0.90; // 10% last-minute discount
        } elseif ($hoursUntilStart <= 72) {
            return 0.95; // 5% short-notice discount
        }
        
        return 1.0;
    }

    private function getExtrasBreakdown(array $extras): array
    {
        $breakdown = [];
        
        foreach ($extras as $extra) {
            $name = $extra['name'] ?? 'Unknown Extra';
            $unitPrice = $extra['unit_price'] ?? 0.00;
            $quantity = $extra['quantity'] ?? 1;
            
            $breakdown[] = [
                'name' => $name,
                'unit_price' => $unitPrice,
                'quantity' => $quantity,
                'total' => $unitPrice * $quantity,
            ];
        }
        
        return $breakdown;
    }

    private function getEquipmentBreakdown(array $equipment): array
    {
        $breakdown = [];
        
        foreach ($equipment as $item) {
            $name = $item['name'] ?? 'Unknown Equipment';
            $dailyRate = $item['daily_rate'] ?? self::EQUIPMENT_DAILY_RATE;
            $days = $item['rental_days'] ?? 1;
            
            $breakdown[] = [
                'name' => $name,
                'daily_rate' => $dailyRate,
                'days' => $days,
                'total' => $dailyRate * $days,
            ];
        }
        
        return $breakdown;
    }
}