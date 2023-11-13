@extends('mails.layout')

@section('body')
<p>
    Bonjour {{ $userName }},
    <br>
    Nous avons reçu une demande de réservation avec référence <strong>{{ $reference }}</strong>, pour
    @if (count($courses) == 1)
        le cours suivant:
    @else
        les cours suivants:
    @endif
</p>

@foreach ($courses as $key => $cType)
    @foreach ($cType as $c)
    <h3>
        {{ count($c['users']) . 'x ' . $c['name'] }}
    </h3>
    <ul>
        <li>
            @if (count($c['dates']) <= 1)
                Date:
            @else
                Dates:
            @endif
            {{ implode(', ', $c['dates']) }}.
        </li>
        <li>
            @if (count($c['users']) <= 1)
                Participant:
            @else
                Participants:
            @endif
            {{ implode(', ', $c['users']) }}.
        </li>
        <li>Moniteur: {{ $c['monitor'] }}.</li>
    </ul>
  @endforeach
@endforeach

@if ($hasCancellationInsurance)
    <h3>+ Remboursement garanti</h3>
@endif

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
    {{ $bookingNotes }}
</p>

<br>

<p>
    Cordialement,
    <br>
    École {{ $schoolName }}
</p>
@endsection
