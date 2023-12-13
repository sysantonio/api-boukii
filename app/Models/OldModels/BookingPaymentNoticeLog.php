<?php

namespace App\Models\OldModels;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;


class BookingPaymentNoticeLog extends Model
{
    protected $table = 'booking_payment_notice_log';
    protected $connection = 'old';

protected $fillable = [
		'booking2_id',
		'booking_user2_id',
		'date'
	];
    public $timestamps = false;

    public static function checkToNotify($data)
    {
        $notify = 1;

        $logs = self::where('booking2_id', $data->booking2_id)
                    ->get();


        $fecha_actual = Carbon::createFromFormat('Y-m-d H:i:s', date("Y-m-d H:i:s"));
        // recorremos los logs para ver si se ha enviado anteriormente el email
        foreach($logs as $log)
        {
            /*
                si han pasado + de 72 horas volvemos a enviar el aviso ya que será un
                aviso de otro curso/clase diferente dentro de la misma reserva
            */
            $fecha_log = Carbon::createFromFormat('Y-m-d H:i:s', $log->date);

            $diff_in_hours = $fecha_log->diffInHours($fecha_actual);
            if ($diff_in_hours <= 72) $notify = 0;
        }

        if($notify==1) return true;
        return false;
    }
}
