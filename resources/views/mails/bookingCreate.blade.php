@extends('mails.layout')

@section('body')
    <p>
        {!! $titleTemplate !!}
    </p>
    <p>
        {{ __('emails.bookingCreate.greeting', ['userName' => $userName]) }},
        <br>
        {{ __('emails.bookingCreate.reservation_request', ['reference' => $reference]) }}
        @if (count($courses) == 1)
            {{ __('emails.bookingCreate.singular_course') }}
        @else
            {{ __('emails.bookingCreate.plural_courses') }}
        @endif
    </p>

    @foreach ($courses as $key => $cType)
        @foreach ($cType as $c)
            <h3>
                {{ __('emails.bookingCreate.course_count', ['count' => count($c['users']), 'name' => $c['name']]) }}
            </h3>
            <ul>
                <li>
                    @if (count($c['dates']) <= 1)
                        {{ __('emails.bookingCreate.singular_date') }}:
                    @else
                        {{ __('emails.bookingCreate.plural_dates') }}:
                    @endif
                    {{ implode(', ', $c['dates']) }}.
                </li>
                <li>
                    @if (count($c['users']) <= 1)
                        {{ __('emails.bookingCreate.singular_participant') }}:
                    @else
                        {{ __('emails.bookingCreate.plural_participants') }}:
                    @endif
                    {{ implode(', ', $c['users']) }}.
                </li>
                <li>{{ __('emails.bookingCreate.instructor', ['monitor' => $c['monitor']]) }}.</li>
            </ul>
        @endforeach
    @endforeach

    @if ($hasCancellationInsurance)
        <h3>{{ __('emails.bookingCreate.refund_guarantee') }}</h3>
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
        {{ __('emails.bookingCreate.sincerely') }},
        <br>
        {{ __('emails.bookingCreate.school_name', ['schoolName' => $schoolName]) }}
    </p>
@endsection
