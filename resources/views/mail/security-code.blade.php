{{-- Email client safe: inline styles and literal colors, no CSS custom properties. --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $headline }}</title>
</head>
<body style="margin:0;padding:24px;background:#0B0B0D;color:#EAE8E1;font-family:Arial,Helvetica,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:520px;margin:0 auto;background:#121214;border:1px solid #242426;border-radius:12px;">
        <tr>
            <td style="padding:24px;">
                <p style="margin:0 0 6px;font-size:12px;letter-spacing:0.08em;text-transform:uppercase;color:#8E8D86;">NampungYuk</p>
                <h1 style="margin:0 0 16px;font-size:19px;line-height:1.3;color:#EAE8E1;">{{ $headline }}</h1>

                @foreach ($lines as $line)
                    <p style="margin:0 0 12px;font-size:14px;line-height:1.6;color:#8E8D86;">{{ $line }}</p>
                @endforeach

                <p style="margin:18px 0;padding:16px 8px;text-align:center;font-family:Consolas,monospace;font-size:30px;letter-spacing:0.35em;background:#161619;border:1px solid #242426;border-radius:10px;color:#EAE8E1;">{{ $code }}</p>

                <p style="margin:0;font-size:12.5px;line-height:1.6;color:#605F5A;">
                    Kode ini berlaku 15 menit dan hanya bisa dipakai sekali. Kalau kamu tidak meminta kode ini, abaikan email ini.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
