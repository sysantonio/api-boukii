@extends('mails.layout')

@section('body')
    <p>
        {!! $titleTemplate !!}
    </p>
    <p>
        {{ __('emails.bookingInfo.greeting', ['userName' => $userName]) }},
        <br>
        {{ __('emails.bookingInfoChange.reservation_request', ['reference' => $reference]) }}
        @if (count($courses) == 1)
            {{ __('emails.bookingInfo.singular_course') }}
        @else
            {{ __('emails.bookingInfo.plural_courses') }}
        @endif
    </p>

    @foreach ($courses as $key => $cType)
        @if($key == 'collective' && count($cType))
            <h1>{{ __('emails.bookingInfo.collective_courses') }}</h1>
        @endif
        @if($key == 'private' && count($cType))
            <h1>{{ __('emails.bookingInfo.private_courses') }}</h1>
        @endif
        @foreach ($cType as $c)
            <h3>
                {{ __('emails.bookingInfo.course_count', ['count' => count($c['users']), 'name' => $c['name']]) }}
            </h3>
            <ul>
                <li>
                    @if (count($c['dates']) <= 1)
                        {{ __('emails.bookingInfo.singular_date') }}:
                    @else
                        {{ __('emails.bookingInfo.plural_dates') }}:
                    @endif
                    {{ implode(', ', $c['dates']) }}.
                </li>
                <li>
                    @if (count($c['users']) <= 1)
                        {{ __('emails.bookingInfo.singular_participant') }}:
                    @else
                        {{ __('emails.bookingInfo.plural_participants') }}:
                    @endif
                    {{ implode(', ', $c['users']) }}.
                </li>
            </ul>
        @endforeach
    @endforeach

    @if ($hasCancellationInsurance)
        <h3>{{ __('emails.bookingInfo.refund_guarantee') }}</h3>
    @endif

    <br>

    <p>
        {{ __('emails.bookingInfo.booking_notes', ['bookingNotes' => $bookingNotes]) }}
    </p>

    <p>
        {!! $bodyTemplate !!}
    </p>


@endsection
