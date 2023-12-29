<?php

namespace App\Repositories;

use App\Models\School;
use App\Repositories\BaseRepository;

class SchoolRepository extends BaseRepository
{
    protected $fieldSearchable = [
'id',
        'name',
        'description',
        'contact_email',
        'contact_phone',
        'contact_telephone',
        'contact_address',
        'contact_cp',
        'contact_city',
        'contact_province',
        'contact_country',
        'fiscal_name',
        'fiscal_id',
        'fiscal_address',
        'fiscal_cp',
        'fiscal_city',
        'fiscal_province',
        'fiscal_country',
        'iban',
        'logo',
        'slug',
        'cancellation_insurance_percent',
        'payrexx_instance',
        'payrexx_key',
        'conditions_url',
        'bookings_comission_cash',
        'bookings_comission_boukii_pay',
        'bookings_comission_other',
        'school_rate',
        'has_ski',
        'has_snowboard',
        'has_telemark',
        'has_rando',
        'inscription',
        'type',
        'active',
        'settings'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return School::class;
    }
}
