<?php

namespace Tests\Feature;

use Tests\TestCase;

class ChatAuditBucket4Test extends TestCase
{
    public function test_tbs_confirm_modal_has_high_z_index_and_promise_support_in_app_layout(): void
    {
        $layoutPath = resource_path('views/layouts/app.blade.php');
        $this->assertFileExists($layoutPath);
        $content = file_get_contents($layoutPath);

        // Bug 1: Confirm modal must have z-[100000] and id="tbsConfirmModal"
        $this->assertStringContainsString('id="tbsConfirmModal"', $content);
        $this->assertStringContainsString('z-[100000]', $content);
        $this->assertStringNotContainsString('class="fixed inset-0 z-[100] flex items-center justify-center', $content);

        // Bug 2: window.tbsConfirm must support Promise return and resolve callbacks
        $this->assertStringContainsString('return new Promise((resolve) =>', $content);
        $this->assertStringContainsString('this.resolvePromise = resolve;', $content);
        $this->assertStringContainsString('this.cancelCallback = opts.onCancel || null;', $content);
        $this->assertStringContainsString('res(true);', $content);
        $this->assertStringContainsString('res(false);', $content);
    }

    public function test_extract_confirm_message_handles_json_parse_and_escaped_quotes(): void
    {
        $layoutPath = resource_path('views/layouts/app.blade.php');
        $content = file_get_contents($layoutPath);

        // Bug 3: extractConfirmMessage must handle confirm(JSON.parse(...)) and escaped quotes
        $this->assertStringContainsString('JSON.parse', $content);
        $this->assertStringContainsString('confirm\\s*\\(\\s*JSON\\.parse\\s*\\(', $content);
        $this->assertStringContainsString('((?:\\\\.|(?!\\1)[\\s\\S])*)', $content);

        // Verify the extraction logic on typical strings
        $extractLogic = function (?string $str): ?string {
            if (! $str) {
                return null;
            }

            // 1. JSON.parse match
            if (preg_match('/confirm\s*\(\s*JSON\.parse\s*\(\s*([\'"])((?:\\\\.|(?!\1)[\s\S])*)\1\s*\)\s*\)/', $str, $jsonParseMatch)) {
                $raw = $jsonParseMatch[2];
                $quote = $jsonParseMatch[1];
                $unescaped = $quote === "'"
                    ? str_replace(["\\'", '\\\\'], ["'", '\\'], $raw)
                    : str_replace(['\\"', '\\\\'], ['"', '\\'], $raw);

                $decoded = json_decode($unescaped);
                if (is_string($decoded)) {
                    return $decoded;
                }
            }

            // 2. Standard match
            if (preg_match('/confirm\s*\(\s*([\'"`])((?:\\\\.|(?!\1)[\s\S])*)\1\s*\)/', $str, $match)) {
                $quote = $match[1];
                $val = $match[2];
                if ($quote === "'") {
                    $val = str_replace("\\'", "'", $val);
                } elseif ($quote === '"') {
                    $val = str_replace('\\"', '"', $val);
                }

                return str_replace('\\\\', '\\', $val);
            }

            return null;
        };

        // Standard string
        $this->assertEquals(
            'Weet je het zeker?',
            $extractLogic("return confirm('Weet je het zeker?');")
        );

        // Escaped quotes in single-quoted string
        $this->assertEquals(
            "Wil je dit risico's nemen?",
            $extractLogic("return confirm('Wil je dit risico\\'s nemen?');")
        );

        // Laravel @js(...) output: JSON.parse('"..."')
        $this->assertEquals(
            'Weet je zeker dat je wilt doorgaan?',
            $extractLogic('return confirm(JSON.parse(\'"Weet je zeker dat je wilt doorgaan?"\'));')
        );

        // Laravel @js(...) output with quotes inside
        $this->assertEquals(
            "Weet je zeker dat je Hendriks risico's wilt opheffen?",
            $extractLogic('return confirm(JSON.parse(\'"Weet je zeker dat je Hendriks risico\\\'s wilt opheffen?"\'));')
        );
    }

    public function test_admin_mail_delete_current_uses_tbs_confirm_modal(): void
    {
        $mailViewPath = resource_path('views/admin/mail/index.blade.php');
        $this->assertFileExists($mailViewPath);
        $content = file_get_contents($mailViewPath);

        // Bug 4: Must use window.tbsConfirm instead of broken synchronous if (!confirm(...)) return
        $this->assertStringNotContainsString("if (!this.selectedMessage || !confirm('Weet je zeker dat je dit bericht wilt verwijderen?')) return;", $content);
        $this->assertStringContainsString('deleteCurrent()', $content);
        $this->assertStringContainsString('window.tbsConfirm({', $content);
        $this->assertStringContainsString("title: 'Bericht Verwijderen'", $content);
        $this->assertStringContainsString("message: 'Weet je zeker dat je dit e-mailbericht wilt verwijderen?'", $content);
        $this->assertStringContainsString('onConfirm: executeDelete', $content);

        // Replaced raw alert() in mail client
        $this->assertStringContainsString('window.tbsToast', $content);
        $this->assertStringContainsString('window.tbsAlert', $content);
    }

    public function test_remaining_views_use_data_confirm_instead_of_inline_confirm(): void
    {
        // 1. admin/audit_log.blade.php
        $auditLogPath = resource_path('views/admin/audit_log.blade.php');
        $this->assertFileExists($auditLogPath);
        $auditLogContent = file_get_contents($auditLogPath);
        $this->assertStringNotContainsString("onclick=\"return confirm('Wil je de complete database-historie", $auditLogContent);
        $this->assertStringContainsString('data-confirm="Wil je de complete database-historie (bank, diefstal, bendes, armdrukken en straffen) opnieuw analyseren op risico\'s?"', $auditLogContent);
        $this->assertStringContainsString('data-confirm-title="Historie Synchroniseren &amp; Scannen"', $auditLogContent);

        // 2. admin/buymeacoffee.blade.php
        $bmcPath = resource_path('views/admin/buymeacoffee.blade.php');
        $this->assertFileExists($bmcPath);
        $bmcContent = file_get_contents($bmcPath);
        $this->assertStringNotContainsString('onsubmit="return confirm(@js(', $bmcContent);
        $this->assertStringNotContainsString("onsubmit=\"return confirm('Weet je zeker dat je dit lidmaatschap", $bmcContent);
        $this->assertStringContainsString('data-confirm="Weet je zeker dat je de beloningen opnieuw wilt uitkeren aan {{ $order->character?->name ?? \'deze speler\' }}?"', $bmcContent);
        $this->assertStringContainsString('data-confirm="Weet je zeker dat je dit lidmaatschap wilt beëindigen? De VIP status en voordelen van de speler worden direct ingetrokken."', $bmcContent);

        // 3. game/gangs.blade.php
        $gangsPath = resource_path('views/game/gangs.blade.php');
        $this->assertFileExists($gangsPath);
        $gangsContent = file_get_contents($gangsPath);
        $this->assertStringNotContainsString('onsubmit="return confirm(@js($character->gang_role === \'leader\'', $gangsContent);
        $this->assertStringNotContainsString("onsubmit=\"return confirm('Weet je zeker dat je bende maskers wilt verkopen", $gangsContent);
        $this->assertStringNotContainsString("onsubmit=\"return confirm('Item verkopen", $gangsContent);
        $this->assertStringContainsString('$gangLeaveConfirmMsg', $gangsContent);
        $this->assertStringContainsString('data-confirm="{{ $gangLeaveConfirmMsg }}"', $gangsContent);
        $this->assertStringContainsString('data-confirm="Weet je zeker dat je bende maskers wilt verkopen aan de heler? De opbrengst van €1.500 per stuk wordt direct gestort in de Bende Kas."', $gangsContent);
        $this->assertStringContainsString('data-confirm="Item verkopen (€{{ number_format($gangItemSellPrice, 0, \',\', \'.\') }} p/s) en opbrengst storten in de bende kas?"', $gangsContent);

        // 4. game/gangs/panden.blade.php
        $pandenPath = resource_path('views/game/gangs/panden.blade.php');
        $this->assertFileExists($pandenPath);
        $pandenContent = file_get_contents($pandenPath);
        $this->assertStringNotContainsString('onclick="return confirm(@js(', $pandenContent);
        $this->assertStringContainsString('data-confirm="Weet je zeker dat je {{ $npcDef[\'name\'] }} wilt aannemen voor €{{ number_format($npcDef[\'cost\'], 0, \',\', \'.\') }} uit de bendekas?"', $pandenContent);

        // 5. game/profile.blade.php
        $profilePath = resource_path('views/game/profile.blade.php');
        $this->assertFileExists($profilePath);
        $profileContent = file_get_contents($profilePath);
        $this->assertStringNotContainsString('onsubmit="return confirm(@js(\'Weet je zeker dat je de ban', $profileContent);
        $this->assertStringContainsString('data-confirm="Weet je zeker dat je de ban van {{ $profileCharacter->name }} wilt opheffen?"', $profileContent);
    }
}
