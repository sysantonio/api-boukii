@extends('mails.layout')

@section('body')
<p>
    Bonjour {{ $userName }},
    <br>
    La réservation avec référence <strong>{{ $reference }}</strong>,
    a été annulé pour
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
    </ul>
  @endforeach
@endforeach

<br>

<p>
    {{ $bookingNotes }}
</p>

<br>

@if (strlen($voucherCode)>3)
<p>
    Un bon d'achat a été généré avec cette annulation.<br>
    Vous avez un solde de {{ $voucherAmount }} CHF avec le code <strong>{{ $voucherCode }}</strong>.
</p>
<br>
@endif

<p>
    Cordialement,
    <br>
    École {{ $schoolName }}
</p>
@endsection
