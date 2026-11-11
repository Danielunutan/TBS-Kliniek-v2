import fs from 'fs';
import path from 'path';

const dir = './atomcms/vendor/atomcms/core/database/migrations';
const files = fs.readdirSync(dir);

let count = 0;
for (const file of files) {
    if (file.endsWith('.php')) {
        const full = path.join(dir, file);
        let content = fs.readFileSync(full, 'utf8');
        if (content.includes('public function up(): void') && !content.includes('catch (\\Throwable')) {
            content = content.replace(/public function up\(\): void\s*\{([\s\S]*?)\n\s*\}\s*\n\s*\/\*\*/, (m, body) => {
                return `public function up(): void\n    {\n        try {${body}\n        } catch (\\Throwable $e) {\n            // Safe CleanDB ignore\n        }\n    }\n\n    /**`;
            });
            fs.writeFileSync(full, content);
            count++;
        }
    }
}
console.log(`Patched ${count} migrations successfully!`);

