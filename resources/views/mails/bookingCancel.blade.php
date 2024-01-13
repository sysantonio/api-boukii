@extends('mails.layout')

@section('body')
    <p>
        {{ trans('emails.bookingCancel.cancellation_greeting', ['userName' => $userName]) }},
        <br>
        {{ trans('emails.bookingCancel.cancellation_intro', ['reference' => $reference]) }}
        @if (count($courses) == 1)
            {{ trans('emails.bookingCancel.single_course') }}
        @else
            {{ trans('emails.bookingCancel.multiple_courses') }}
        @endif
    </p>

    @foreach ($courses as $key => $cType)
        @foreach ($cType as $c)
            <h3>
                {{ trans('emails.bookingCancel.course_count', ['count' => count($c['users']), 'name' => $c['name']]) }}
            </h3>
            <ul>
                <li>
                    @if (count($c['dates']) <= 1)
                        {{ trans('emails.bookingCancel.single_date') }}:
                    @else
                        {{ trans('emails.bookingCancel.multiple_dates') }}:
                    @endif
                    {{ implode(', ', $c['dates']) }}.
                </li>
                <li>
                    @if (count($c['users']) <= 1)
                        {{ trans('emails.bookingCancel.single_user') }}:
                    @else
                        {{ trans('emails.bookingCancel.multiple_users') }}:
                    @endif
                    {{ implode(', ', $c['users']) }}.
                </li>
            </ul>
        @endforeach
    @endforeach

    <br>

    <p>
        {{ trans('emails.bookingCancel.booking_notes', ['bookingNotes' => $bookingNotes]) }}
    </p>

    <br>

    <p>
        {{ trans('emails.bookingCancel.cancellation_regards', ['schoolName' => $schoolName]) }}
    </p>
@endsection
