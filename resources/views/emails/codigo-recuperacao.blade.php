<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0; padding:0; background-color:#f4f6f5; font-family: Arial, sans-serif;">
<div style="max-width:480px; margin:40px auto; background:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.06);">

    <div style="background-color:#2E5E43; padding:24px; text-align:center;">
        <h1 style="color:#ffffff; margin:0; font-size:20px;">Recuperação de senha</h1>
    </div>

    <div style="padding:32px 24px;">
        <p style="color:#333333; font-size:15px; line-height:1.5; margin:0 0 20px;">
            Você solicitou a recuperação da sua senha. Use o código abaixo para continuar:
        </p>

        <div style="background-color:#f0f4f2; border-radius:8px; padding:20px; text-align:center; margin-bottom:20px;">
                <span style="font-size:32px; font-weight:bold; letter-spacing:8px; color:#2E5E43;">
                    {{ $codigo }}
                </span>
        </div>

        <p style="color:#777777; font-size:13px; line-height:1.5; margin:0;">
            Este código expira em <strong>15 minutos</strong>. Se você não solicitou a recuperação de senha, ignore este email.
        </p>
    </div>

    <div style="background-color:#fafafa; padding:16px 24px; text-align:center;">
        <p style="color:#999999; font-size:12px; margin:0;">
            Este é um email automático, não responda.
        </p>
    </div>

</div>
</body>
</html>
