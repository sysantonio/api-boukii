@extends('mails.newLayout')

@section('body')
    <table width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#d9d9d9">
        <tr>
            <td align="center">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center" class="email-container">
                    <tr>
                        <td class="center-on-narrow" style="padding:30px 0px;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center" width="580" style="margin: auto; width:580px" class="email-container">
                                <tr>
                                    <td class="center-on-narrow" align="left" valign="middle" style="font-size:24px; line-height:29px;" >
                                        <font face="Arial, Helvetica, sans-serif" style="font-size:24px; line-height:29px; color:#000000; font-weight:bold; text-transform: uppercase;">   Confirmación de Reserva</font>
                                    </td>

                                    <td width="50" class="center-on-narrow" align="right" valign="middle" style="font-size:24px; line-height:29px;" >
                                        <font face="Arial, Helvetica, sans-serif" style="font-size:24px; line-height:29px; color:#ed1b66; font-weight:bold; text-transform: uppercase;">#{{$reference}}</font>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <center style="width: 100%;">
        <table width="100%" cellpadding="0" cellspacing="0" border="0" class="center-on-narrow">
            <tr>
                <td valign="top" align="center" style="padding-top:20px; padding-bottom:15px;">
                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center" width="580" style="margin: auto; width:580px" class="email-container">
                        <tr>
                            <td class="center-on-narrow">
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center" width="580" style="margin: auto; width:580px" class="email-container">
                                    <tr>
                                        <td class="left-on-narrow" align="left" valign="middle">
                                            <font face="Arial, Helvetica, sans-serif" style="font-size:16px; line-height:21px; color:#000000;">
                                                {!! $titleTemplate !!}
                                            </font>
                                        </td>
                                    </tr>
                                    <tr>

                                        <td class="left-on-narrow" align="left" valign="middle">
                                            <font face="Arial, Helvetica, sans-serif" style="font-size:16px; line-height:21px; color:#000000;">
                                                Hello "<strong>Nombre Cliente</strong>"
                                                <br><br>
                                                Gracias por reservar con nosotros tu curso o actividad.
                                                <br><br>
                                                Te enviamos este mensaje para confirmarte la reserva <strong>#{{$reference}}</strong>, para las siguientes actividades/cursos.
                                            </font>
                                            <br><br>
                                            <font face="Arial, Helvetica, sans-serif" style="font-size:14px; line-height:19px; color:#000000;">
                                                Nota: El código QR de la reserva es de uso exclusivo de la escuela. Presentalo cuando te lo soliciten.
                                            </font>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </center>
    <center style="width: 100%;">
        <table width="100%" cellpadding="0" cellspacing="0" border="0" class="center-on-narrow">
            <tr>
                <td valign="top" align="center" style="padding-top:20px; padding-bottom:0px;">
                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center" width="580" style="margin: auto; width:580px" class="email-container">
                        <tr>
                            <td align="center" valign="top" style="padding:15px 20px 15px 20px;" bgcolor="#f4f4f4">

                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                    <tr>
                                        <td>
                                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                                <tr>
                                                    <td align="left" valign="middle" style="font-size:24px; line-height:29px; padding:0px 0px 15px 0px;" >
                                                        <font face="Arial, Helvetica, sans-serif" style="font-size:24px; line-height:29px; color:#d2d2d2; font-weight:bold;">Actividad</font>
                                                    </td>
                                                    <td width="50" align="right" valign="middle" style="font-size:24px; line-height:29px; padding:0px 0px 15px 0px;" >
                                                        <font face="Arial, Helvetica, sans-serif" style="font-size:24px; line-height:29px; color:#d9d9d9; font-weight:bold;">01</font>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>

                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                    <tr>
                                        <td valign="top" class="left-on-narrow">
                                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                                <tr>
                                                    <td width="100" align="left" style="font-size:16px; line-height:21px; padding:0px 0px;" >
                                                        <font face="Arial, Helvetica, sans-serif" style="font-size:16px; line-height:21px; color:#000000;">Nombre</font>
                                                    </td>
                                                    <td align="left" style="font-size:18px; line-height:23px; padding:0px 0px;" >
                                                        <font face="Arial, Helvetica, sans-serif" style="font-size:18px; line-height:23px; color:#000000; font-weight:bold;">Privé Mat 25.03.24</font>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td width="100" align="left" style="font-size:14px; line-height:19px; padding:0px 0px;" >
                                                        <font face="Arial, Helvetica, sans-serif" style="font-size:14px; line-height:19px; color:#000000;">Tipo:</font>
                                                    </td>
                                                    <td align="left" style="font-size:14px; line-height:19px; padding:0px 0px;" >
                                                        <font face="Arial, Helvetica, sans-serif" style="font-size:14px; line-height:19px; color:#000000;">Cours Prive Ski</font>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td width="100" align="left" style="font-size:14px; line-height:19px; padding:0px 0px;" >
                                                        <font face="Arial, Helvetica, sans-serif" style="font-size:14px; line-height:19px; color:#000000;">Date:</font>
                                                    </td>
                                                    <td align="left" style="font-size:14px; line-height:19px; padding:0px 0px;" >
                                                        <font face="Arial, Helvetica, sans-serif" style="font-size:14px; line-height:19px; color:#000000;">April 18, 2024 - 09:00 / 09:45</font>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td width="100" align="left" style="font-size:14px; line-height:19px; padding:0px 0px;" >
                                                        <font face="Arial, Helvetica, sans-serif" style="font-size:14px; line-height:19px; color:#000000;">Participante:</font>
                                                    </td>
                                                    <td align="left" style="font-size:14px; line-height:19px; padding:0px 0px;" >
                                                        <font face="Arial, Helvetica, sans-serif" style="font-size:14px; line-height:19px; color:#000000;"><strong>Cliente Demo</strong> ES - España - 29 Años</font>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td width="100" align="left" style="font-size:14px; line-height:19px; padding:0px 0px;" >
                                                        <font face="Arial, Helvetica, sans-serif" style="font-size:14px; line-height:19px; color:#000000;">Monitor:</font>
                                                    </td>
                                                    <td align="left" style="font-size:14px; line-height:19px; padding:0px 0px;" >
                                                        <font face="Arial, Helvetica, sans-serif" style="font-size:14px; line-height:19px; color:#000000;"><strong>Cliente Demo</strong> ES - España - 29 Años</font>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                        <td valign="top" width="110" class="left-on-narrow">
                                            <img src="images/codigo-qr.gif" width="110" height="110" alt="" border="0"  style="display: block;  height: auto; max-width: 110px; max-height:110px; font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#152a69; line-height:20px; vertical-align:bottom;margin-top: 15px; margin-bottom: 15px;" />
                                        </td>
                                    </tr>
                                </table>

                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                    <tr>
                                        <td style="padding: 10px 0px;">
                                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                                <tr>
                                                    <td style="width: 100%; border-bottom: 1px solid #d2d2d2;"></td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>

                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                    <tr>
                                        <td>
                                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                                <tr>
                                                    <td align="left" valign="middle" style="font-size:24px; line-height:29px; padding-bottom:10px;" >
                                                        <font face="Arial, Helvetica, sans-serif" style="font-size:24px; line-height:29px; color:#d2d2d2; font-weight:bold;">Prix</font>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>

                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                    <tr>
                                        <td valign="top">
                                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                                <tr>
                                                    <td width="100" align="left" style="font-size:14px; line-height:19px; padding:0px 0px;" >
                                                        <font face="Arial, Helvetica, sans-serif" style="font-size:14px; line-height:19px; color:#000000;">Precio base</font>
                                                    </td>
                                                    <td align="right" style="font-size:14px; line-height:19px; padding:0px 0px;" >
                                                        <font face="Arial, Helvetica, sans-serif" style="font-size:14px; line-height:19px; color:#000000;">65 CHF</font>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td width="100" align="left" style="font-size:14px; line-height:19px; padding:0px 0px;" >
                                                        <font face="Arial, Helvetica, sans-serif" style="font-size:14px; line-height:19px; color:#000000;">Extras (x1)</font>
                                                    </td>
                                                    <td align="right" style="font-size:14px; line-height:19px; padding:0px 0px;" >
                                                        <font face="Arial, Helvetica, sans-serif" style="font-size:14px; line-height:19px; color:#000000;">32 CHF</font>
                                                    </td>
                                                </tr>
                                            </table>

                                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                                <tr>
                                                    <td style="padding: 10px 0px;">
                                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                                            <tr>
                                                                <td style="width: 100%; border-bottom: 1px solid #d2d2d2;"></td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>
                                            </table>

                                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                                <tr>
                                                    <td width="100" align="left" style="font-size:16px; line-height:21px; font-weight: bold; padding:0px 0px;" >
                                                        <font face="Arial, Helvetica, sans-serif" style="font-size:16px; line-height:21px; color:#000000; font-weight: bold;">Total</font>
                                                    </td>
                                                    <td align="right" style="font-size:16px; line-height:21px; color:#000000; font-weight: bold; padding:0px 0px;" >
                                                        <font face="Arial, Helvetica, sans-serif" style="font-size:16px; line-height:21px; color:#000000; font-weight: bold;">97.00 CHF</font>
                                                    </td>
                                                </tr>
                                            </table>

                                        </td>
                                    </tr>
                                </table>

                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </center>

    @foreach ($courses as $key=>$type)
        @if($key == 1 && count($type))
            <h1>{{ __('emails.bookingInfo.collective_courses') }}</h1>
        @endif
        @if($key == 2 && count($type))
            <h1>{{ __('emails.bookingInfo.private_courses') }}</h1>
        @endif
        <h3>
            {{ trans('emails.bookingCancel.course_count', ['count' => count($type),
            'name' => $type->first()->first()->first()->first()->first()['course']['name']]) }}
        </h3>
        @foreach ($type as $keyClient => $client)
            <li>
                {{ __('emails.bookingInfo.singular_participant') }}
                {{ $client->first()->first()->first()->first()['client']['full_name'] }}.
            </li>
            @if(isset($client->first()->first()->first()->first()['courseExtras'][0]))
                <li>
                    {{ __('emails.bookingInfo.extras') }}:  {{ __('emails.bookingInfo.forfait') }}
                    {{ $client->first()->first()->first()->first()['courseExtras'][0]['description'] }}.
                </li>
            @endif
            @if($key == 1 && count($type))
                <li>  {{ __('emails.bookingInfo.degree') }}
                    : {{ $client->first()->first()->first()->first()['degree']['name'] }}</li>
            @endif
            <li>
                @if (count($client->first()->first()) <= 1)
                    {{ __('emails.bookingInfo.singular_date') }}
                @else
                    {{ __('emails.bookingInfo.plural_dates') }}
                @endif
            </li>
            @foreach ($client as $courseKey=>$course)
                @foreach ($course as $keyDegree => $degree)
                    @foreach ($degree as $keyDate => $courseDate)
                        {{ \Carbon\Carbon::parse($courseDate[0]['date'])->format('d-m-Y') }}
                        {{$courseDate[0]['hour_start']}}  - {{$courseDate[0]['hour_end']}}
                        <br>
                        <li>{{ __('emails.bookingCreate.instructor', ['monitor' => isset($courseDate[0]['monitor']) ?
                               $courseDate[0]['monitor']['full_name'] : __('emails.bookingInfo.unknown')]) }}
                            .
                        </li>
                    @endforeach

                @endforeach
            @endforeach
            <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                <tbody>
                <tr>
                    <td align="center">
                        <a href="{{ $client->first()->first()->first()->first()['client']['id'] }}" target="_blank"><img src="https://chart.googleapis.com/chart?chs=300x300&cht=qr&choe=UTF-8&chl={{ $client->first()->first()->first()->first()['client']['id'] }}"></a>
                    </td>
                </tr>
                </tbody>
            </table>
            <hr>

        @endforeach
    @endforeach

    @if ($hasCancellationInsurance)
        <h3>{{ __('emails.bookingCreate.refund_guarantee') }}</h3>
    @endif

    <br>

    @if ($paid)
        <h3>{{ __('emails.bookingCreate.not_paid') }}</h3>
    @endif

    <br>

    <p>
        {{ __('emails.bookingCreate.booking_notes', ['bookingNotes' => $bookingNotes]) }}
    </p>

    <br>

    <p>
        {!! $bodyTemplate !!}
    </p>

    <p>
        {{ __('emails.bookingCreate.sincerely') }}
        <br>
        {{ __('emails.bookingCreate.school_name', ['schoolName' => $schoolName]) }}
    </p>
@endsection
