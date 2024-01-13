<div class="footer">
    <table role="presentation" border="0" cellpadding="0" cellspacing="0">
        <tbody>
        <tr>
            <td class="content-block">
                @if ($actionURL)
                    <p>
                        {{ trans('emails.footer.button_copy_url') }}
                        <br>
                        <a href="{{ $actionURL }}">{{ $actionURL }}</a>
                    </p>
                @endif

                <p>
                    {{ trans('emails.footer.automatic_email') }}
                    <br>
                    @if ($schoolEmail)
                        {{ trans('emails.footer.contact_school', ['schoolName' => $schoolName]) }}
                        <a href="mailto:{{ $schoolEmail }}">{{ $schoolEmail }}</a>.
                    @else
                        {{ trans('emails.footer.contact_boukii') }}
                        <a href="https://boukii.ch/contact-3/">Centro de Contacto de Boukii</a>.
                    @endif
                </p>

                <p>
                    {{ trans('emails.footer.copyright') }}
                    @if ($schoolConditionsURL)
                        <a href="{{ $schoolConditionsURL }}">{{ trans('emails.footer.school_conditions') }}</a>
                    @else
                        <a href="https://boukii.ch/conditions-generales/">{{ trans('emails.footer.boukii_conditions') }}</a>
                    @endif
                </p>
            </td>
        </tr>
        </tbody>
    </table>
</div>
