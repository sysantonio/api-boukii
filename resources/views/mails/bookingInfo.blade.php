@extends('mails.layout')

@section('body')
    <p>
        {!! $titleTemplate !!}
    </p>
    <p>
        {{ __('emails.bookingInfo.greeting', ['userName' => $userName]) }},
        <br>
        {{ __('emails.bookingInfo.reservation_request', ['reference' => $reference]) }}
        @if (count($courses) == 1)
            {{ __('emails.bookingInfo.singular_course') }}
        @else
            {{ __('emails.bookingInfo.plural_courses') }}
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
