<tr>
    <td class="header">
        <a href="{{ config('app.frontend_url', config('app.url')) }}" style="display: inline-block;">
            @if (trim($slot) === 'Laravel')
                <span
                    style="color: #f5f5f5; font-size: 32px; font-weight: 900; letter-spacing: 0.35em; text-transform: uppercase; font-family: Georgia, 'Times New Roman', serif;">
                    M<span style="color: #dc2626;">A</span>FIA
                </span>
            @else
                {{ $slot }}
            @endif
        </a>
    </td>
</tr>