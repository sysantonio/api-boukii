<?php

namespace App\Repositories;

use App\Models\Monitor;
use App\Repositories\BaseRepository;

class MonitorRepository extends BaseRepository
{
    protected $fieldSearchable = [
'id',
        'email',
        'first_name',
        'last_name',
        'birth_date',
        'phone',
        'telephone',
        'address',
        'cp',
        'city',
        'province',
        'country',
        'language1_id',
        'language2_id',
        'language3_id',
        'image',
        'avs',
        'work_license',
        'bank_details',
        'children',
        'civil_status',
        'family_allowance',
        'partner_work_license',
        'partner_works',
        'partner_percentaje',
        'user_id',
        'active',
        'active_school'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return Monitor::class;
    }
}
