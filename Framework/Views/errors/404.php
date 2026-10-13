<?php
/** @var string $message Set in catchException() via require (same scope). */
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>404 - Nicht gefunden</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f8fafc; color: #334155; padding: 60px; text-align: center; }
        .error-container { max-width: 500px; margin: 0 auto; background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        h1 { color: #3b82f6; font-size: 48px; margin: 0; }
        p { color: #64748b; font-size: 16px; margin: 20px 0; }
        a { display: inline-block; background: #3b82f6; color: white; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: 500; }
        a:hover { background: #2563eb; }
    </style>
</head>
<body>
    <div class="error-container">
        <h1>404</h1>
        <p>Die von dir aufgerufene Seite konnte leider nicht gefunden werden.</p>
        <code><?php echo $message; ?></code>
        <a href="/">Zur Startseite</a>
    </div>
</body>
</html>
