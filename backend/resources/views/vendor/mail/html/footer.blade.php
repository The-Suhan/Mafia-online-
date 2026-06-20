<tr>
    <td>
        <table class="footer" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
            <tr>
                <td class="content-cell" align="center">
                    {{ Illuminate\Mail\Markdown::parse($slot) }}
                    <p>&copy; {{ date('Y') }} Mafia Game. {{ __('All rights reserved.') }}</p>
                    <p style="font-size: 11px; letter-spacing: 0.15em;">SOME SECRETS ARE BEST KEPT IN THE FAMILY.</p>
                </td>
            </tr>
        </table>
    </td>
</tr>