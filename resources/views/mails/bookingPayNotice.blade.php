@extends('mails.layout')

@section('body')
    <p>
        {!! $titleTemplate !!}
    </p>
    <p>
        {{ __('emails.bookingPay.greeting', ['userName' => $userName]) }},
        <br>
        {{ __('emails.bookingPay.payment_deadline', ['reference' => $reference]) }}
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
                <a href="{{ $actionURL }}" target="_blank"><img src="https://chart.googleapis.com/chart?chs=300x300&cht=qr&choe=UTF-8&chl={{ $actionURL }}"></a>
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
