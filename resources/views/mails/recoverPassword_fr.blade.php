@extends('mails.layout')

@section('body')
<p>
    Bonjour {{ $userName }},
    <br>
    Pour réinitialiser votre mot de passe dans Boukii suivez le lien suivant.
</p>

<br>

<table role="presentation" border="0" cellpadding="0" cellspacing="0">
    <tbody>
        <tr>
            <td align="center">
                <a href="{{ $actionURL }}" target="_blank">Réinitialiser mon mot de passe</a>
            </td>
        </tr>
    </tbody>
</table>

<br>

<p>
  Si vous ne souhaitez pas réinitialiser votre mot de passe, ignorez cet e-mail. Le mot de passe ne sera pas modifié.
</p>

<p>
    Cordialement,
    <br>
    L'équipe Boukii
</p>
@endsection