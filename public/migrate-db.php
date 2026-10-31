<?php
/**
 * TBS Kliniek - One-Click Database & Production Setup Script
 * Beveiligd met geheime omgevingssleutel (DB_SETUP_KEY).
 */

// Bootstrap Laravel and environment
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$secretKey = (string) (env('DB_SETUP_KEY') ?? getenv('DB_SETUP_KEY') ?? '');
$providedKey = (string) ($_GET['key'] ?? $_POST['key'] ?? '');

if (empty($secretKey) || ! hash_equals($secretKey, $providedKey)) {
    http_response_code(403);
    ?>
    <!DOCTYPE html>
    <html lang="nl">
    <head>
        <meta charset="UTF-8">
        <title>Toegang Geweigerd - TBS Kliniek</title>
        <style>
            body { font-family: system-ui, sans-serif; background: #0b0f19; color: #f8fafc; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
            .box { background: #1e293b; border: 1px solid #334155; padding: 2rem; border-radius: 1rem; max-width: 480px; text-align: center; box-shadow: 0 10px 25px rgba(0,0,0,0.5); }
            h1 { color: #f43f5e; margin-top: 0; font-size: 1.5rem; }
            p { color: #94a3b8; font-size: 0.95rem; line-height: 1.5; }
            code { background: #0f172a; color: #38bdf8; padding: 0.2rem 0.5rem; border-radius: 0.25rem; }
        </style>
    </head>
    <body>
        <div class="box">
            <h1>🔒 Toegang Geweigerd</h1>
            <?php if (empty($secretKey)) { ?>
                <p>De web-installer is uitgeschakeld. Configureer een veilige <code>DB_SETUP_KEY</code> in uw <code>.env</code> bestand of voer migraties uit via het commandline interface (CLI).</p>
            <?php } else { ?>
                <p>Dit script is beveiligd met een geautoriseerde sleutel. Geef de sleutel op via de URL parameter:</p>
                <p><code>/migrate-db.php?key=[uw_geheime_sleutel]</code></p>
            <?php } ?>
        </div>
    </body>
    </html>
    <?php
    exit;
}

$action = $_POST['action'] ?? '';
$outputLog = '';
$dbStatus = null;
$dbError = null;

// Test DB Connection
try {
    DB::connection()->getPdo();
    $isLocal = app()->environment('local');
    $dbName = htmlspecialchars(DB::connection()->getDatabaseName() ?? '');
    $dbStatus = $isLocal
        ? 'OK ('.$dbName.' op '.htmlspecialchars((string) config('database.connections.'.config('database.default').'.host', 'localhost')).')'
        : 'OK ('.$dbName.')';
} catch (Throwable $e) {
    $dbError = app()->environment('local') ? $e->getMessage() : 'Kan geen verbinding maken met de database. Controleer de configuratie in .env.';
}

// Handle Self-Delete
if ($action === 'delete') {
    @unlink(__FILE__);
    ?>
    <!DOCTYPE html>
    <html lang="nl">
    <head>
        <meta charset="UTF-8">
        <title>Verwijderd - TBS Kliniek</title>
        <style>
            body { font-family: system-ui, sans-serif; background: #0b0f19; color: #f8fafc; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
            .box { background: #1e293b; border: 1px solid #10b981; padding: 2.5rem; border-radius: 1rem; text-align: center; max-width: 480px; }
            h1 { color: #10b981; margin-top: 0; }
            a { display: inline-block; margin-top: 1.5rem; background: #2563eb; color: #fff; text-decoration: none; padding: 0.75rem 1.5rem; border-radius: 0.5rem; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class="box">
            <h1>✅ Veilig Verwijderd!</h1>
            <p>Het installatiescript <code>migrate-db.php</code> is succesvol verwijderd van de server voor optimale veiligheid.</p>
            <a href="/">Ga naar TBS Kliniek</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Handle Migration & Seed Actions
if ($action === 'run_all' || $action === 'run_migrate' || $action === 'run_seed') {
    try {
        if ($action === 'run_all' || $action === 'run_migrate') {
            Artisan::call('migrate --force');
            $outputLog .= "=== PHP ARTISAN MIGRATE ===\n".Artisan::output()."\n\n";
        }

        if ($action === 'run_all' || $action === 'run_seed') {
            Artisan::call('db:seed --force');
            $outputLog .= "=== PHP ARTISAN DB:SEED ===\n".Artisan::output()."\n\n";
        }

        // Cache setup
        @Artisan::call('storage:link');
        @Artisan::call('config:clear');
        @Artisan::call('cache:clear');
        @Artisan::call('view:clear');
        $outputLog .= "=== SYSTEEM OPTIMALISATIE ===\nStorage linked & cache ververst!\n";
    } catch (Throwable $e) {
        $msg = app()->environment('local') ? $e->getMessage()."\n".$e->getTraceAsString() : $e->getMessage();
        $outputLog .= "\nFOUT TIJDENS UITVOERING:\n".$msg;
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TBS Kliniek - Database & Installatie Wizard</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #090d16; color: #f1f5f9; padding: 2rem 1rem; margin: 0; }
        .container { max-width: 780px; margin: 0 auto; background: #131b2e; border: 1px solid #1e293b; border-radius: 1.25rem; padding: 2rem; box-shadow: 0 20px 40px rgba(0,0,0,0.6); }
        .header { display: flex; align-items: center; gap: 1rem; border-bottom: 1px solid #1e293b; padding-bottom: 1.5rem; margin-bottom: 1.5rem; }
        .header img { width: 52px; height: 52px; border-radius: 0.75rem; }
        .header h1 { margin: 0; font-size: 1.5rem; color: #fff; }
        .header p { margin: 0.25rem 0 0; color: #94a3b8; font-size: 0.875rem; }
        .card { background: #0b1120; border: 1px solid #1e293b; border-radius: 0.75rem; padding: 1.25rem; margin-bottom: 1.5rem; }
        .badge { display: inline-block; padding: 0.25rem 0.6rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; }
        .badge-success { background: #064e3b; color: #34d399; border: 1px solid #059669; }
        .badge-danger { background: #4c0519; color: #fb7185; border: 1px solid #e11d48; }
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; font-weight: 700; font-size: 0.95rem; padding: 0.85rem 1.5rem; border-radius: 0.6rem; cursor: pointer; border: none; text-decoration: none; transition: all 0.2s; }
        .btn-primary { background: #2563eb; color: #fff; width: 100%; }
        .btn-primary:hover { background: #1d4ed8; }
        .btn-danger { background: #dc2626; color: #fff; }
        .btn-danger:hover { background: #b91c1c; }
        pre { background: #050811; border: 1px solid #1e293b; border-radius: 0.5rem; padding: 1rem; color: #38bdf8; font-family: monospace; font-size: 0.85rem; overflow-x: auto; white-space: pre-wrap; }
        .steps { margin: 1rem 0; padding-left: 1.25rem; color: #94a3b8; font-size: 0.9rem; line-height: 1.7; }
        .steps strong { color: #f8fafc; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="/assets/logo.png" alt="TBS Kliniek">
            <div>
                <h1>TBS Kliniek - Database Installatie Wizard</h1>
                <p>Voer database migraties en seeders uit in productie zonder SSH-toegang.</p>
            </div>
        </div>

        <div class="card">
            <h3 style="margin-top:0; margin-bottom: 0.75rem;">Database Verbindingsstatus:</h3>
            <?php if ($dbStatus) { ?>
                <span class="badge badge-success">✓ Verbonden</span>
                <p style="margin: 0.5rem 0 0; color: #94a3b8; font-size: 0.9rem;">Database: <strong><?= htmlspecialchars($dbStatus) ?></strong></p>
            <?php } else { ?>
                <span class="badge badge-danger">✗ Geen verbinding</span>
                <p style="margin: 0.5rem 0 0; color: #fb7185; font-size: 0.9rem;">Foutmelding: <code><?= htmlspecialchars($dbError) ?></code></p>
                <div style="margin-top: 1rem; background: #1e1b4b; border: 1px solid #3730a3; padding: 1rem; border-radius: 0.5rem; font-size: 0.85rem; color: #c7d2fe;">
                    <strong>Tip voor Plesk:</strong><br>
                    1. Ga in Plesk naar <strong>Databases</strong> ➔ maak een MySQL database + databasegebruiker aan.<br>
                    2. Ga naar <strong>Files</strong> ➔ open <code>.env</code> in <code>httpdocs</code>.<br>
                    3. Vul je <code>DB_DATABASE</code>, <code>DB_USERNAME</code> en <code>DB_PASSWORD</code> in.<br>
                    4. Ververs daarna deze pagina!
                </div>
            <?php } ?>
        </div>

        <?php if ($outputLog) { ?>
            <div class="card">
                <h3 style="margin-top:0; color: #34d399;">Resultaat van uitvoering:</h3>
                <pre><?= htmlspecialchars($outputLog) ?></pre>
                <div style="margin-top: 1rem; display: flex; gap: 1rem; align-items: center; justify-content: space-between;">
                    <a href="/" class="btn" style="background: #10b981; color: #fff;">🎉 Naar de website</a>
                    <form method="POST" style="margin: 0;">
                        <input type="hidden" name="key" value="<?= htmlspecialchars($providedKey) ?>">
                        <input type="hidden" name="action" value="delete">
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Weet je zeker dat je dit script wilt verwijderen?')">🗑️ Verwijder dit script van de server</button>
                    </form>
                </div>
            </div>
        <?php } ?>

        <?php if ($dbStatus && ! str_contains($outputLog, 'PHP ARTISAN MIGRATE')) { ?>
            <div class="card">
                <h3 style="margin-top:0;">Actie uitvoeren:</h3>
                <p style="color: #94a3b8; font-size: 0.9rem;">Klik op de onderstaande knop om alle tabellen aan te maken (migraties) en de initiële content (frikandellen, gangs, missies, items) in te laden.</p>
                <form method="POST">
                    <input type="hidden" name="key" value="<?= htmlspecialchars($providedKey) ?>">
                    <input type="hidden" name="action" value="run_all">
                    <button type="submit" class="btn btn-primary" onclick="this.innerHTML='Even geduld... Bezig met installeren...'; this.style.opacity='0.7';">
                        🚀 Start Volledige Database Installatie (Migrate + Seed)
                    </button>
                </form>
            </div>
        <?php } ?>

        <div style="font-size: 0.8rem; color: #64748b; text-align: center; margin-top: 2rem;">
            TBS Kliniek Game Engine &bull; Productie Setup Wizard
        </div>
    </div>
</body>
</html>
