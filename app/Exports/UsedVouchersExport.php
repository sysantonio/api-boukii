<?php

namespace App\Exports;

use App\Models\VouchersLog;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMapping;

class UsedVouchersExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    protected $from;
    protected $to;

    public function __construct($from, $to)
    {
        $this->from = $from;
        $this->to = $to;
    }

    public function collection(): Collection
    {
        return VouchersLog::with(['voucher.client', 'booking'])
            ->whereHas('voucher') // esto filtra los que tienen voucher
            ->whereBetween('created_at', [$this->from, $this->to])
            ->get();
    }

    public function headings(): array
    {
        return [
            'Código Voucher',
            'Cliente',
            'Email Cliente',
            'ID Reserva',
            'Fecha uso',
            'Cantidad usada',
            'Estado',
            'Creación del voucher'
        ];
    }

    public function map($log): array
    {
        return [
            $log->voucher->code ?? '',
            optional($log->voucher->client)->full_name ?? '',
            optional($log->voucher->client)->email ?? '',
            $log->booking_id,
            $log->created_at->format('Y-m-d H:i:s'),
            $log->amount,
            $log->status,
            $log->voucher->created_at,
        ];
    }
}
