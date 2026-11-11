import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';
import { createRequire } from 'module';
const require = createRequire(import.meta.url);
const pako = require('pako');
const { PNG } = require('pngjs');

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

/**
 * TBS-Kliniek DMCA-vrije Nitro Asset Transformatie Pipeline
 * Unpacks .nitro bundels, muteert pixel-art spritesheets en metadata,
 * en exporteert geldige tbs_*.nitro bestanden zonder isometrische offsets te breken.
 */

// TBS-Kliniek kleurprofielen voor pixel mutatie
export const TBS_PALETTES = {
    // TBS Kliniek gevangenis uniform oranje met verweerde schaduwen
    PRISON_ORANGE: {
        targetHue: 25, // Oranje tint
        saturationBoost: 1.25,
        contrast: 1.15,
        shadingTint: [45, 18, 5],
        highlightTint: [255, 140, 40],
    },
    // Kliniek medisch / isolatiecel staalblauw en mintgroen
    CLINICAL_MINT: {
        targetHue: 165,
        saturationBoost: 0.85,
        contrast: 1.1,
        shadingTint: [15, 35, 30],
        highlightTint: [160, 240, 210],
    },
    // Donkere misdaad cartel / bende tactische stealth tinten
    CARTEL_STEALTH: {
        targetHue: 210,
        saturationBoost: 0.6,
        contrast: 1.3,
        shadingTint: [10, 14, 20],
        highlightTint: [80, 95, 120],
    },
    // Smokkelgoud en luxe buit
    SMUGGLED_GOLD: {
        targetHue: 45,
        saturationBoost: 1.4,
        contrast: 1.2,
        shadingTint: [60, 40, 5],
        highlightTint: [255, 220, 90],
    },
};

/**
 * Converteer RGB naar HSL
 */
function rgbToHsl(r, g, b) {
    r /= 255;
    g /= 255;
    b /= 255;
    const max = Math.max(r, g, b);
    const min = Math.min(r, g, b);
    let h = 0;
    let s = 0;
    const l = (max + min) / 2;

    if (max !== min) {
        const d = max - min;
        s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
        switch (max) {
            case r:
                h = (g - b) / d + (g < b ? 6 : 0);
                break;
            case g:
                h = (b - r) / d + 2;
                break;
            case b:
                h = (r - g) / d + 4;
                break;
        }
        h /= 6;
    }
    return [h * 360, s, l];
}

/**
 * Converteer HSL naar RGB
 */
function hslToRgb(h, s, l) {
    h /= 360;
    let r, g, b;

    if (s === 0) {
        r = g = b = l;
    } else {
        const hue2rgb = (p, q, t) => {
            if (t < 0) t += 1;
            if (t > 1) t -= 1;
            if (t < 1 / 6) return p + (q - p) * 6 * t;
            if (t < 1 / 2) return q;
            if (t < 2 / 3) return p + (q - p) * (2 / 3 - t) * 6;
            return p;
        };

        const q = l < 0.5 ? l * (1 + s) : l + s - l * s;
        const p = 2 * l - q;
        r = hue2rgb(p, q, h + 1 / 3);
        g = hue2rgb(p, q, h);
        b = hue2rgb(p, q, h - 1 / 3);
    }

    return [Math.round(r * 255), Math.round(g * 255), Math.round(b * 255)];
}

/**
 * Unpack een .nitro binary bundle buffer naar losse bestanden
 */
export function unpackNitro(buffer) {
    const ab = buffer.buffer.slice(buffer.byteOffset, buffer.byteOffset + buffer.byteLength);
    const view = new DataView(ab);
    let pos = 0;

    const fileCount = view.getInt16(pos);
    pos += 2;

    const files = {};

    for (let i = 0; i < fileCount; i++) {
        const nameLen = view.getInt16(pos);
        pos += 2;

        const nameBytes = ab.slice(pos, pos + nameLen);
        pos += nameLen;
        const name = new TextDecoder().decode(nameBytes);

        const fileLen = view.getInt32(pos);
        pos += 4;

        const compressedBytes = new Uint8Array(ab.slice(pos, pos + fileLen));
        pos += fileLen;

        const decompressed = pako.inflate(compressedBytes);

        if (name.endsWith('.json')) {
            files[name] = {
                type: 'json',
                name,
                content: JSON.parse(new TextDecoder().decode(decompressed)),
            };
        } else {
            files[name] = {
                type: 'png',
                name,
                buffer: Buffer.from(decompressed),
            };
        }
    }

    return files;
}

/**
 * Pack bestanden terug naar een geldige .nitro binary bundle buffer
 */
export function packNitro(files) {
    const entries = Object.values(files);
    const parts = [];

    // 1. Aantal bestanden (Short 2 bytes)
    const headerBuf = Buffer.alloc(2);
    headerBuf.writeInt16BE(entries.length, 0);
    parts.push(headerBuf);

    for (const entry of entries) {
        // Bestandsnaam in bytes
        const nameBytes = Buffer.from(entry.name, 'utf8');
        const nameLenBuf = Buffer.alloc(2);
        nameLenBuf.writeInt16BE(nameBytes.length, 0);
        parts.push(nameLenBuf);
        parts.push(nameBytes);

        // Data comprimeren met pako deflate
        let rawData;
        if (entry.type === 'json') {
            rawData = Buffer.from(JSON.stringify(entry.content));
        } else {
            rawData = entry.buffer;
        }

        const compressed = Buffer.from(pako.deflate(rawData));
        const fileLenBuf = Buffer.alloc(4);
        fileLenBuf.writeInt32BE(compressed.length, 0);
        parts.push(fileLenBuf);
        parts.push(compressed);
    }

    return Buffer.concat(parts);
}

/**
 * Transformeer spritesheet pixels (DMCA-vrij kleurenpalet en verweerde shading)
 */
export function mutateSpritesheet(pngBuffer, paletteKey = 'PRISON_ORANGE') {
    const palette = TBS_PALETTES[paletteKey] || TBS_PALETTES.PRISON_ORANGE;
    const png = PNG.sync.read(pngBuffer);
    const data = png.data;

    for (let i = 0; i < data.length; i += 4) {
        const a = data[i + 3];
        if (a === 0) continue; // Skip transparante pixels

        let r = data[i];
        let g = data[i + 1];
        let b = data[i + 2];

        // Isometrische zwarte/donkere omtreklijnen behouden met TBS diepte
        const luminance = 0.299 * r + 0.587 * g + 0.114 * b;
        if (luminance < 30) {
            // Zeer donkere omtrek behouden voor strakke pixel art
            data[i] = Math.max(0, r - 5);
            data[i + 1] = Math.max(0, g - 5);
            data[i + 2] = Math.max(0, b - 5);
            continue;
        }

        // Converteer naar HSL
        const [h, s, l] = rgbToHsl(r, g, b);

        // Pas nieuw doelpalet toe met behoud van relatieve schaduwniveaus
        const newHue = palette.targetHue;
        const newSat = Math.min(1, Math.max(0, s * palette.saturationBoost));
        let newLight = l;

        // Pas contrast toe
        newLight = (newLight - 0.5) * palette.contrast + 0.5;
        newLight = Math.min(1, Math.max(0, newLight));

        // Converteer terug naar RGB
        const [newR, newG, newB] = hslToRgb(newHue, newSat, newLight);

        // Meng subtiele shading tint voor verweerde klinieklook
        if (newLight < 0.4) {
            data[i] = Math.round(newR * 0.7 + palette.shadingTint[0] * 0.3);
            data[i + 1] = Math.round(newG * 0.7 + palette.shadingTint[1] * 0.3);
            data[i + 2] = Math.round(newB * 0.7 + palette.shadingTint[2] * 0.3);
        } else if (newLight > 0.8) {
            data[i] = Math.round(newR * 0.75 + palette.highlightTint[0] * 0.25);
            data[i + 1] = Math.round(newG * 0.75 + palette.highlightTint[1] * 0.25);
            data[i + 2] = Math.round(newB * 0.75 + palette.highlightTint[2] * 0.25);
        } else {
            data[i] = newR;
            data[i + 1] = newG;
            data[i + 2] = newB;
        }
    }

    return PNG.sync.write(png);
}

/**
 * Transformeer een enkel .nitro bestand naar een tbs_* DMCA-vrij item
 */
export function transformAsset(inputNitroPath, outputDir, options = {}) {
    const prefix = options.prefix || 'tbs_';
    const palette = options.palette || 'PRISON_ORANGE';

    const originalFilename = path.basename(inputNitroPath, '.nitro');
    const newName = `${prefix}${originalFilename}`;

    const inputBuffer = fs.readFileSync(inputNitroPath);
    const files = unpackNitro(inputBuffer);

    let jsonEntry = null;
    let pngEntry = null;

    for (const key of Object.keys(files)) {
        if (files[key].type === 'json') jsonEntry = files[key];
        if (files[key].type === 'png') pngEntry = files[key];
    }

    if (!jsonEntry || !pngEntry) {
        throw new Error(`Ongeldige Nitro bundel zonder json of png: ${inputNitroPath}`);
    }

    // 1. Muteer de spritesheet pixels
    const mutatedPngBuffer = mutateSpritesheet(pngEntry.buffer, palette);

    // 2. Muteer JSON metadata en referenties
    const json = jsonEntry.content;
    const oldName = json.name || originalFilename;

    json.name = newName;
    if (json.type) json.type = newName;

    const newPngName = `${newName}.png`;
    const newJsonName = `${newName}.json`;

    if (json.spritesheet && json.spritesheet.meta) {
        json.spritesheet.meta.image = newPngName;
    }

    // 3. Bouw de gemuteerde bundel
    const outputFiles = {
        [newJsonName]: {
            type: 'json',
            name: newJsonName,
            content: json,
        },
        [newPngName]: {
            type: 'png',
            name: newPngName,
            buffer: mutatedPngBuffer,
        },
    };

    const packedBuffer = packNitro(outputFiles);

    if (!fs.existsSync(outputDir)) {
        fs.mkdirSync(outputDir, { recursive: true });
    }

    const outputPath = path.join(outputDir, `${newName}.nitro`);
    fs.writeFileSync(outputPath, packedBuffer);

    return {
        originalName: oldName,
        transformedName: newName,
        outputPath,
        sizeBytes: packedBuffer.length,
    };
}

/**
 * Batch verwerking voor mappen
 */
export function batchTransformDirectory(inputDir, outputDir, options = {}) {
    const files = fs.readdirSync(inputDir).filter(f => f.endsWith('.nitro'));
    console.log(`[TBS-Kliniek Pipeline] Gevonden: ${files.length} Nitro bundels om te transformeren`);

    const results = [];
    const limit = options.limit || files.length;

    for (let i = 0; i < Math.min(files.length, limit); i++) {
        const file = files[i];
        const inputPath = path.join(inputDir, file);
        try {
            const result = transformAsset(inputPath, outputDir, options);
            results.push(result);
            console.log(`[OK ${i + 1}/${limit}] ${file} -> ${result.transformedName}.nitro (${result.sizeBytes} bytes)`);
        } catch (e) {
            console.error(`[FOUT] ${file}:`, e.message);
        }
    }

    return results;
}

// Test / CLI uitvoering wanneer direct aangeroepen
if (process.argv[1] === fileURLToPath(import.meta.url)) {
    const testInput = path.join(__dirname, 'assets', 'furniture', 'nitro', 'ads_aftv_chair.nitro');
    const testOutputDir = path.join(__dirname, 'assets', 'furniture', 'tbs');

    if (fs.existsSync(testInput)) {
        console.log(`[Test] Transformeer enkel item: ${testInput}`);
        const res = transformAsset(testInput, testOutputDir, { palette: 'PRISON_ORANGE' });
        console.log(`[Succes] Gecreeerd:`, res);

        // Verifieer unpack van het getransformeerde bestand
        const verifyBuffer = fs.readFileSync(res.outputPath);
        const unpacked = unpackNitro(verifyBuffer);
        console.log(`[Verificatie] Unpacked sleutels in nieuw bestand:`, Object.keys(unpacked));
    } else {
        console.log(`[Info] Testbestand niet gevonden op: ${testInput}`);
    }
}
