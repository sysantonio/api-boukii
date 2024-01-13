@extends('mails.layout')

@section('body')
    <p>
        {{ trans('emails.welcome', ['userName' => $userName]) }}
    </p>

    <br>

    <p>
        {{ trans('emails.regards') }}
    </p>
@endsection
