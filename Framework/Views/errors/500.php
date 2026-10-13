<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>500 - Serverfehler</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f8fafc; color: #334155; padding: 40px; }
        .error-container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border-top: 4px solid #ef4444; }
        h1 { color: #ef4444; margin-top: 0; font-size: 24px; }
        pre { background: #f1f5f9; padding: 15px; border-radius: 6px; overflow-x: auto; font-size: 13px; }
        .meta { color: #64748b; font-size: 14px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="error-container">
        <h1>500 - Interner Serverfehler</h1>
        <p class="meta">Hoppla, da ist im Framework etwas schiefgelaufen.</p>

        <?php if (!empty($debug)): ?>
            <div style="margin-top: 20px;">
                <h3>Debug-Informationen:</h3>
                <p><strong>Nachricht:</strong> <?= htmlspecialchars($exception->getMessage()) ?></p>
                <p><strong>Datei:</strong> <?= $exception->getFile() ?> (Zeile: <?= $exception->getLine() ?>)</p>
                
                <h3>Stacktrace:</h3>
                <pre><?= htmlspecialchars($exception->getTraceAsString()) ?></pre>
            </div>
        <?php else: ?>
            <p>Bitte versuche es später noch einmal oder kontaktiere den Administrator.</p>
        <?php endif; ?>
    </div>
</body>
</html>
