import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const configDir = path.join(__dirname, 'volt-client', 'configuration');
const examplePath = path.join(configDir, 'renderer-config.json.example.json');
const targetPath = path.join(configDir, 'renderer-config.json');

const ex = JSON.parse(fs.readFileSync(examplePath, 'utf8'));

// Update host URLs while preserving all ${...} references exactly
ex['socket.url'] = 'ws://127.0.0.1:2096';
ex['api.url'] = 'http://127.0.0.1:3000';
ex['asset.url'] = 'http://127.0.0.1:3000/hotel-files/assets';
ex['image.library.url'] = 'http://127.0.0.1:3000/hotel-files/c_images/';
ex['hof.furni.url'] = 'http://127.0.0.1:3000/hotel-files/c_images/hof_furni';
ex['images.url'] = 'http://127.0.0.1:3000/hotel-files/images';
ex['gamedata.url'] = 'http://127.0.0.1:3000/hotel-files/gamedata';
ex['asset.bundle.format'] = 'hab';
ex['furnidata.version'] = '1';

fs.writeFileSync(targetPath, JSON.stringify(ex, null, 2), 'utf8');

console.log('Fixed renderer-config.json successfully!');
for (const [k, v] of Object.entries(ex)) {
  if (typeof v === 'string' && (v.includes('${') || v.includes('%'))) {
    console.log(k, '=>', v);
  }
}
