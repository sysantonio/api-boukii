@extends('mails.layout')

@section('body')
    <p>
        {!! $titleTemplate !!}
    </p>
    <p>
        {{ trans('emails.bookingCancel.cancellation_greeting', ['userName' => $userName]) }},
        <br>
        {!! trans('emails.bookingCancel.cancellation_intro', ['reference' => $reference]) !!}
        @if (count($courses) == 1)
            {{ trans('emails.bookingCancel.single_course') }}
        @else
            {{ trans('emails.bookingCancel.multiple_courses') }}
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
                        {{ \Carbon\Carbon::parse($courseDate[0]['date'])->format('d-m-Y') }}
                        {{$courseDate[0]['hour_start']}}  - {{$courseDate[0]['hour_end']}}
                        <br>
                        <li>{{ __('emails.bookingCreate.instructor', ['monitor' => isset($courseDate[0]['monitor']) ? $courseDate[0]['monitor']['full_name'] : 'unknown']) }}
                            .
                        </li>

                    @endforeach

                @endforeach
            @endforeach
            <hr>

        @endforeach
    @endforeach
    <br>

    <p>
        {{ trans('emails.bookingCancel.booking_notes', ['bookingNotes' => $bookingNotes]) }}
    </p>

    <br>

    <p>
        {!! $bodyTemplate !!}
    </p>

    <p>
        {{ trans('emails.bookingCancel.cancellation_regards', ['schoolName' => $schoolName]) }}
    </p>
@endsection
