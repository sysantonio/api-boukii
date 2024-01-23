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
                        {{ \Carbon\Carbon::parse($courseDate[0]['courseDate']['date'])->format('d-m-Y') }}
                        {{$courseDate[0]['courseDate']['hour_start']}}  - {{$courseDate[0]['courseDate']['hour_end']}}
                        <br>
                        <li>{{ __('emails.bookingCreate.instructor', ['monitor' => isset($courseDate[0]['monitor']) ?
                               $courseDate[0]['monitor']['full_name'] : __('emails.bookingInfo.unknown')]) }}
                            .
                        </li>

                    @endforeach

                @endforeach
            @endforeach
            <hr>

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
