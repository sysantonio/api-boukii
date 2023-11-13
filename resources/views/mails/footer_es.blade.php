<div class="footer">
    <table role="presentation" border="0" cellpadding="0" cellspacing="0">
        <tbody>
            <tr>
                <td class="content-block">
                    @if ($actionURL)
                    <p>
                        ¿No funciona el botón de enlace? Copia la url para acceder
                        <br>
                        <a href="{{ $actionURL }}">{{ $actionURL }}</a>
                    </p>
                    @endif

                    <p>
                        Este correo electrónico se ha generado automáticamente y no puede recibir respuestas.
                        <br>
                        @if ($schoolEmail)
                            Para más información, <a href="mailto:{{ $schoolEmail }}">contacta con la escuela {{ $schoolName }}</a>.
                        @else
                            Para más información, visita el <a href="https://boukii.ch/contact-3/">Centro de Contacto de Boukii</a>.
                        @endif
                    </p>

                    <p>
                        Boukii © 2022
                        @if ($schoolConditionsURL)
                        <a href="{{ $schoolConditionsURL }}">Condiciones generales de venta de la escuela</a>
                        @else
                            <a href="https://boukii.ch/conditions-generales/">Condiciones de uso</a>
                        @endif
                    </p>
                </td>
            </tr>
        </tbody>
    </table>
</div>
