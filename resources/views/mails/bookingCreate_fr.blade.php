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
