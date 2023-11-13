@extends('mails.layout')

@section('body')
<p>
    Hola {{ $userName }},
    <br>
    Hemos recibido una solicitud de reserva con referencia <strong>{{ $reference }}</strong>, para
    @if (count($courses) == 1)
        el siguiente curso:
    @else
        los siguientes cursos:
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
                Fecha:
            @else
                Fechas:
            @endif
            {{ implode(', ', $c['dates']) }}.
        </li>
        <li>
            @if (count($c['users']) <= 1)
                Participante:
            @else
                Participantes:
            @endif
            {{ implode(', ', $c['users']) }}.
        </li>
        <li>Monitor: {{ $c['monitor'] }}.</li>
    </ul>
  @endforeach
@endforeach

@if ($hasCancellationInsurance)
    <h3>+ Garantía de reembolso</h3>
@endif

<br>

<p>
    {{ $bookingNotes }}
</p>

<br>

<p>
    Atentamente,
    <br>
    La escuela {{ $schoolName }}
</p>
@endsection
