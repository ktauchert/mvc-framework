# ADR-0011: PHP 8.4+, durchgängig strikte Typen, modernes OOP

- Status: Accepted
- Datum: 2026-10-10

## Kontext

Der Kern wird selbst geschrieben, um PHP-OOP zu lernen und zugleich ernst zu bleiben (0008). Ohne festgelegte Sprachebene rutscht der Code in lose Arrays und PHP 5-Stil.

## Entscheidung

- **PHP ≥ 8.4** (8.5 ist ok).
- Jede PHP-Datei: `declare(strict_types=1);` unmittelbar nach `<?php`.
- PSR-4-Namespaces (`Framework\`, `App\`), keine Dateien ohne Namespace außer `public/index.php`.
- Sichtbare Typen: Properties, Parameter, Return. Constructor Property Promotion und `readonly`, wo der Wert sich nicht ändert.
- `enum` für geschlossene Mengen (HTTP-Methode), `match` statt langen `switch`, wo es klarer ist.
- Kein `@`-Fehlerunterdrücken, kein untypisiertes `mixed` als Bequemlichkeit.
- Property Hooks und andere 8.4-Extras: nutzen, wenn sie Klarheit bringen, nicht um sie zu nutzen.

## Folgen

Jede Phase im [Umsetzungsplan](../roadmap.md) hat einen PHP-Lernfokus. Reviews prüfen Typen und Namespace, nicht nur „läuft“.
