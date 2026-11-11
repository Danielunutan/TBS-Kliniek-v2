import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const targetPath = path.join(__dirname, 'assets', 'gamedata', 'json', 'ExternalTexts.json');

if (fs.existsSync(targetPath)) {
    console.log('Rebranding ExternalTexts.json naar TBS-Kliniek...');
    const raw = fs.readFileSync(targetPath, 'utf8');
    const texts = JSON.parse(raw);

    // Kernvervangingen
    texts['landing.view.welcome'] = 'Welkom in de TBS-Kliniek';
    texts['navigator.title'] = 'Celblokken & Territoria';
    texts['navigator.public'] = 'Kliniek Afdelingen';
    texts['navigator.all'] = 'Spelers Cellen & Bendes';
    texts['navigator.my'] = 'Mijn Cellen';
    texts['catalog.title'] = 'Smokkelmarkt & Buit';
    texts['inventory.title'] = 'Smokkelrugzak';
    texts['memenu.title'] = 'Patiëntendossier';
    texts['purse.credits'] = 'Zwart Geld';
    texts['purse.pixels'] = 'Smokkelwaar';
    texts['purse.points'] = 'Buit';
    texts['purse.hc'] = 'Cartel VIP';
    texts['room.spectator.mode'] = 'Camerabewaking';

    // Massale rebrandings in sleutels
    for (const [key, value] of Object.entries(texts)) {
        if (typeof value === 'string') {
            texts[key] = value
                .replace(/Habbo Hotel/gi, 'TBS-Kliniek')
                .replace(/Habbo Club/gi, 'Cartel VIP')
                .replace(/Habbo's/gi, 'Patiënten')
                .replace(/Habbo/gi, 'TBS-Kliniek');
        }
    }

    fs.writeFileSync(targetPath, JSON.stringify(texts), 'utf8');
    console.log('ExternalTexts.json succesvol gerebrand naar TBS-Kliniek!');
} else {
    console.error('Bestand niet gevonden:', targetPath);
}
