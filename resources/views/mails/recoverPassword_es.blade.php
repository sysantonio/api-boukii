@extends('mails.layout')

@section('body')
<p>
    Hola {{ $userName }},
    <br>
    Para resetear tu contraseña en Boukii sigue el siguiente enlace.
</p>

<br>

<table role="presentation" border="0" cellpadding="0" cellspacing="0">
    <tbody>
        <tr>
            <td align="center">
                <a href="{{ $actionURL }}" target="_blank">Resetear mi contraseña</a>
            </td>
        </tr>
    </tbody>
</table>

<br>

<p>
  Si no quieres restablecer tu contraseña, puedes ignorar este correo. La contraseña no se cambiará.
</p>

<p>
    Atentamente,
    <br>
    El equipo Boukii
</p>
@endsection