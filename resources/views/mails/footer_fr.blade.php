<div class="footer">
    <table role="presentation" border="0" cellpadding="0" cellspacing="0">
        <tbody>
            <tr>
                <td class="content-block">
                    @if ($actionURL)
                    <p>
                        Le lien ne fonctionne pas? Copiez l'URL pour accéder à
                        <br>
                        <a href="{{ $actionURL }}">{{ $actionURL }}</a>
                    </p>
                    @endif

                    <p>
                        Cet e-mail a été généré automatiquement et ne peut pas recevoir de réponses.
                        <br>
                        @if ($schoolEmail)
                            Pour plus d'informations, <a href="mailto:{{ $schoolEmail }}">contacter l'école {{ $schoolName }}</a>.
                        @else
                            Pour plus d'informations, visitez notre <a href="https://boukii.ch/contact-3/">Centre de Contact de Boukii</a>.
                        @endif
                    </p>

                    <p>
                        Boukii © 2022
                        @if ($schoolConditionsURL)
                            <a href="{{ $schoolConditionsURL }}">Conditions générales de vente de l'école</a>
                        @else
                            <a href="https://boukii.ch/conditions-generales/">Conditions d'utilisation</a>
                        @endif
                    </p>
                </td>
            </tr>
        </tbody>
    </table>
</div>
