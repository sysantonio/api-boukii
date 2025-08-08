<?php

namespace App\V5\Modules\Booking\Requests;

use App\V5\Modules\Booking\Models\Booking;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Booking Filters Request Validation
 */
class BookingFiltersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Pagination
            'page' => ['integer', 'min:1'],
            'limit' => ['integer', 'min:1', 'max:100'],
            
            // Filters
            'type' => ['nullable', 'string', 'in:' . implode(',', Booking::getValidTypes())],
            'status' => ['nullable', 'string', 'in:' . implode(',', Booking::getValidStatuses())],
            'client_id' => ['nullable', 'integer', 'exists:clients,id'],
            'course_id' => ['nullable', 'integer', 'exists:courses,id'],
            'monitor_id' => ['nullable', 'integer', 'exists:monitors,id'],
            
            // Date filters
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'date_range' => ['nullable', 'array'],
            'date_range.start' => ['required_with:date_range', 'date'],
            'date_range.end' => ['required_with:date_range', 'date', 'after_or_equal:date_range.start'],
            
            // Price filters
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0', 'gte:min_price'],
            
            // Boolean filters
            'has_insurance' => ['nullable', 'boolean'],
            'has_equipment' => ['nullable', 'boolean'],
            
            // Search
            'search' => ['nullable', 'string', 'max:255'],
            
            // Sorting
            'sort_by' => ['nullable', 'string', 'in:created_at,start_date,total_price,client_name,status'],
            'sort_direction' => ['nullable', 'string', 'in:asc,desc'],
            
            // Include relationships
            'with' => ['nullable', 'array'],
            'with.*' => ['string', 'in:client,course,monitor,season,school,extras,equipment,payments'],
        ];
    }

    public function messages(): array
    {
        return [
            'page.integer' => 'Page must be a valid integer',
            'page.min' => 'Page must be at least 1',
            'limit.integer' => 'Limit must be a valid integer',
            'limit.min' => 'Limit must be at least 1',
            'limit.max' => 'Limit cannot exceed 100',
            'type.in' => 'Invalid booking type',
            'status.in' => 'Invalid booking status', 
            'client_id.exists' => 'Selected client does not exist',
            'course_id.exists' => 'Selected course does not exist',
            'monitor_id.exists' => 'Selected monitor does not exist',
            'start_date.date' => 'Start date must be a valid date',
            'end_date.date' => 'End date must be a valid date',
            'end_date.after_or_equal' => 'End date must be after or equal to start date',
            'date_range.array' => 'Date range must be an array',
            'date_range.start.required_with' => 'Start date is required when using date range',
            'date_range.end.required_with' => 'End date is required when using date range',
            'date_range.end.after_or_equal' => 'End date must be after or equal to start date',
            'min_price.numeric' => 'Minimum price must be a valid number',
            'min_price.min' => 'Minimum price cannot be negative',
            'max_price.numeric' => 'Maximum price must be a valid number',
            'max_price.min' => 'Maximum price cannot be negative',
            'max_price.gte' => 'Maximum price must be greater than or equal to minimum price',
            'search.max' => 'Search query cannot exceed 255 characters',
            'sort_by.in' => 'Invalid sort field',
            'sort_direction.in' => 'Sort direction must be asc or desc',
            'with.array' => 'Include relationships must be an array',
            'with.*.in' => 'Invalid relationship to include',
        ];
    }

    public function attributes(): array
    {
        return [
            'page' => 'page number',
            'limit' => 'results per page',
            'type' => 'booking type',
            'status' => 'booking status',
            'client_id' => 'client',
            'course_id' => 'course',
            'monitor_id' => 'monitor',
            'start_date' => 'start date',
            'end_date' => 'end date',
            'min_price' => 'minimum price',
            'max_price' => 'maximum price',
            'has_insurance' => 'has insurance',
            'has_equipment' => 'has equipment',
            'search' => 'search query',
            'sort_by' => 'sort field',
            'sort_direction' => 'sort direction',
            'with' => 'include relationships',
        ];
    }

    /**
     * Get validated filters for repository
     */
    public function getFilters(): array
    {
        return $this->only([
            'type',
            'status', 
            'client_id', 
            'course_id', 
            'monitor_id',
            'start_date', 
            'end_date', 
            'date_range',
            'min_price', 
            'max_price',
            'has_insurance', 
            'has_equipment',
            'search'
        ]);
    }

    /**
     * Get pagination parameters
     */
    public function getPagination(): array
    {
        return [
            'page' => $this->input('page', 1),
            'limit' => $this->input('limit', 20),
        ];
    }

    /**
     * Get relationships to include
     */
    public function getIncludes(): array
    {
        return $this->input('with', ['client', 'course', 'monitor']);
    }

    /**
     * Get sorting parameters
     */
    public function getSorting(): array
    {
        return [
            'sort_by' => $this->input('sort_by', 'created_at'),
            'sort_direction' => $this->input('sort_direction', 'desc'),
        ];
    }
}