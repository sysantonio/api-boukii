@extends('mails.layout')

@section('body')
<p>
    Bonjour {{ $userName }},<br>
    Vous avez 24 heures pour effectuer le paiement de la réservation <strong>{{ $reference }}</strong>.
</p>

<p>
    Pour compléter cette réservation, vous devez payer <strong>{{ $amount }} {{ $currency }}</strong>.
    <br>
    Pour cela, scannez ou cliquez sur ce QR.
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
    Cordialement,
    <br>
    École {{ $schoolName }}
</p>
@endsection