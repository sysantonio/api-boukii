@extends('mails.layout')

@section('body')
    <p>
        {!! $titleTemplate !!}
    </p>
    <p>
        {{ __('emails.bookingPay.greeting', ['userName' => $userName]) }},
        <br>
        {{ __('emails.bookingPay.reservation_request', ['reference' => $reference]) }}
        @if (count($courses) == 1)
            {{ __('emails.bookingPay.singular_course') }}
        @else
            {{ __('emails.bookingPay.plural_courses') }}
        @endif
    </p>

    @foreach ($courses as $key => $cType)
        @foreach ($cType as $c)
            <h3>
                {{ __('emails.bookingPay.course_count', ['count' => count($c['users']), 'name' => $c['name']]) }}
            </h3>
            <ul>
                <li>
                    @if (count($c['dates']) <= 1)
                        {{ __('emails.bookingPay.singular_date') }}:
                    @else
                        {{ __('emails.bookingPay.plural_dates') }}:
                    @endif
                    {{ implode(', ', $c['dates']) }}.
                </li>
                <li>
                    @if (count($c['users']) <= 1)
                        {{ __('emails.bookingPay.singular_participant') }}:
                    @else
                        {{ __('emails.bookingPay.plural_participants') }}:
                    @endif
                    {{ implode(', ', $c['users']) }}.
                </li>
                <li>{{ __('emails.bookingPay.instructor', ['monitor' => $c['monitor']]) }}.</li>
            </ul>
        @endforeach
    @endforeach

    @if ($hasCancellationInsurance)
        <h3>{{ __('emails.bookingPay.refund_guarantee') }}</h3>
    @endif

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
        {{ __('emails.bookingPay.booking_notes', ['bookingNotes' => $bookingNotes]) }}
    </p>

    <br>

    <p>
        {!! $bodyTemplate !!}
    </p>

    <p>
        {{ __('emails.bookingPay.sincerely', ['schoolName' => $schoolName]) }}
    </p>
@endsection
