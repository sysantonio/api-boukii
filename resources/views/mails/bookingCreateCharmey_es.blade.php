@extends('mails.layout')

@section('body')
    <p>
        Hola {{ $userName }},
        <br>
        Gracias por tu reserva en ESS Charmey con referencia <strong>{{ $reference }}</strong>, para
        @if (count($courses) == 1)
            el siguiente curso:
        @else
            los siguientes cursos:
        @endif
    </p>

    @foreach ($courses as $key => $cType)
       @if($key == 'collective' && count($cType))
            <h1>Cursos Colectivos</h1>
        @endif
        @if($key == 'private' && count($cType))
            <h1>Cursos Privados</h1>
        @endif
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
            </ul>
        @endforeach
        <br>
       @if($key == 'collective' && count($cType))
            <div>
                <h4>Horario de los cursos:</h4>
                <div>
                    <u>Cursos de la mañana</u>
                    <li>
                        9:10 a. m.: encuentro en la estación del teleférico, donde los niños serán atendidos por el monitor
                    </li>
                    <li>
                        9:45 a. m.: inicio del curso
                    </li>
                    <li>
                        11:45 a. m.: fin del curso en la cima del teleférico
                    </li>
                    <br>
                    <u>Cursos de la tarde</u>
                    <li>
                        1:45 p. m.: encuentro en la cima del teleférico, donde los niños serán atendidos por el monitor
                    </li>
                    <li>
                        2:00 p. m.: inicio del curso
                    </li>
                    <li>
                        4:00 p. m.: fin del curso en la cima del teleférico
                    </li>
                </div>
                <br>
                <h4>Otra información</h4>
                <div>
                    <li>Nuestros cursos colectivos se imparten únicamente en francés.</li>
                    <li>No alquilamos material ni en la recepción ni en la cima de los remontes mecánicos. Le recomendamos que se dirija a una tienda de deportes cerca de usted o a <a target="_blank" href="https://www.charmeysports.ch/"> Charmey Sports</a>. Tenga en cuenta que no le recomendamos alquilar material el primer día de cursos colectivos, ya que podría perder la cita de las 9:10 a. m.
                    </li>
                </div>
                <br>
                <h4>Consejos para los padres</h4>
                <div>
                    <li>Unos días antes del curso, explique a su hijo que tomará un curso con otros niños y un monitor para que pueda prepararse.</li>
                    <li>Considere vestir a su hijo según el clima. Le recordamos que es obligatorio llevar un traje de esquí, guantes, casco y gafas. ¡No olvide la crema solar!</li>
                    <li>Contamos con su puntualidad, tanto al comienzo como al final del curso.</li>
                    <li>Le pedimos que no siga el curso para que su hijo no se distraiga y pueda concentrarse en los ejercicios.</li>
                    <li>El objetivo es que el niño se divierta en el curso y progrese a su propio ritmo. ¡El aprendizaje debe ser un placer!</li>
                </div>
            </div>
        @endif
        @if($key == 'private' && count($cType))
            <div>
                <h4>Horario de los cursos:</h4>
                <div>
                    <p>Te esperamos 5 minutos antes del inicio del curso en la cima del teleférico. Ten en cuenta que se tarda aproximadamente 15 minutos en subir.
                    </p>
                    <p>En caso de retraso, no podemos garantizar la duración del curso originalmente programado. En días de gran afluencia, no dudes en pasar por delante de la cola presentándote como alumno de la escuela de esquí, esto te permitirá llegar a tiempo al curso.
                    </p>
                    <p>Para ahorrar tiempo, no dudes en comprar tu pase en línea: https://shop.e-guma.ch/telecharmey/fr/tickets</p>
                </div>
                <br>
                <h4>Información específica para los niños</h4>
                <ul>
                    <li>Menos de 6 años: el viaje de ida y vuelta es gratuito y es obligatorio acompañar al niño en la cabina.</li>
                    <li>De 6 a 8 años: el viaje de ida y vuelta es de pago y el niño puede tomar la cabina solo si tiene el permiso por escrito de un padre (que debe entregarse en la recepción).</li>
                    <li>A partir de 8 años: el viaje de ida y vuelta es de pago y el niño puede tomar la cabina solo.</li>
                </ul>
            </div>
        @endif
        <br>
    @endforeach

    @if ($hasCancellationInsurance)
        <h3>+ Reembolso garantizado</h3>
    @endif

    <br>

    <p>
        {{ $bookingNotes }}
    </p>

    <br>
    <h1>Condiciones de cancelación</h1>
    <p>
        En caso de modificación o cancelación, agradecemos que nos avises lo antes posible al +41 (0)26 927 55 25.
    </p>
    <p>
        En caso de ausencia del alumno al inicio del curso, el precio del mismo no será reembolsado y el curso no se cambiará por otro.
    </p>
    <p>
        En caso de enfermedad o accidente, el cliente se compromete a informar de la ausencia lo antes posible, como máximo 1 hora antes del inicio del curso. Solo se podrá aplazar o reembolsar si se eligió la opción de reembolso al hacer la reserva. Esta opción se puede agregar dentro de las 24 horas posteriores a la reserva solo por correo electrónico a la dirección ess@charmey.ch.
    </p>

    Si un curso debe ser cancelado, por ejemplo, debido al cierre del teleférico, te avisaremos por SMS. Hacemos todo lo posible para reubicar los cursos en la estación de Jaun, a 10 minutos en coche de la salida del pueblo de Charmey.

    <p>
        ¡Gracias por tu inscripción y esperamos darte la bienvenida!
    </p>
    <p>
        El equipo de la Escuela Suiza de Esquí Charmey
    </p>
    <p>ess@charmey.ch</p>
    <p>
        +41 (0)26 927 55 25
    </p>
@endsection
