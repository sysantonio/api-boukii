@extends('mails.layout')

@section('body')
    <p>
        {!! $titleTemplate !!}
    </p>
    <p>
        {{ __('emails.bookingPay.greeting', ['userName' => $userName]) }},
        <br>
        {!!  __('emails.bookingNoticePay.payment_deadline', ['reference' => $reference]) !!}
    </p>

    <p>
        {{ __('emails.bookingPay.payment_notice', ['amount' => $amount, 'currency' => $currency]) }}
        <br>
        {{ __('emails.bookingPay.payment_instructions') }}
    </p>

    <br>

    <table role="presentation" border="0" cellpadding="0" cellspacing="0">
        <tbody>
        <tr>
            <td align="center">
                <!-- El código QR es ahora un enlace clicable -->
                <a href="{{ $actionURL }}" target="_blank">
                    <img src="data:image/png;base64,{{ base64_encode(\QrCode::format('png')->size(110)->generate($actionURL)) }}" alt="QR Code" style="width: 110px; height: 110px;">
                </a>
            </td>
        </tr>
        </tbody>
    </table>

    <br>

    <p>
        {!! $bodyTemplate !!}
    </p>


    <p>
        {{ __('emails.bookingPay.regards') }},
        <br>
        {{ __('emails.bookingPay.school_name', ['schoolName' => $schoolName]) }}
    </p>
@endsection
