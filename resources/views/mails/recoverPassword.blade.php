@extends('mails.layout')

@section('body')
    <p>
        {{ trans('emails.recoverPassword.reset_password_greeting', ['userName' => $userName]) }},
        <br>
        {{ trans('emails.recoverPassword.reset_password_intro') }}
    </p>

    <br>

    <table role="presentation" border="0" cellpadding="0" cellspacing="0">
        <tbody>
        <tr>
            <td align="center">
                <a href="{{ $actionURL }}" target="_blank">{{ trans('emails.recoverPassword.reset_password_button') }}</a>
            </td>
        </tr>
        </tbody>
    </table>

    <br>

    <p>
        {{ trans('emails.recoverPassword.reset_password_outro') }}
    </p>

    <p>
        {{ trans('emails.recoverPassword.reset_password_regards') }}
    </p>
@endsection
