import mysql from 'mysql2/promise';

async function revert() {
    const conn = await mysql.createConnection({
        host: '127.0.0.1',
        user: 'root',
        password: '',
        database: 'habbo',
    });

    console.log('Verbonden met habbo database');

    // 1. Herstel outfit
    await conn.query('UPDATE users SET look = ? WHERE username = ?', [
        'hr-115-42.hd-190-1.ch-215-62.lg-270-62.sh-290-62',
        'Daniel'
    ]);

    // 2. Herstel kamer 57
    await conn.query('UPDATE rooms SET name = ?, description = ?, paper_floor = ?, paper_wall = ? WHERE id = 57', [
        'dddd',
        '',
        '0.0',
        '0.0'
    ]);

    // 3. Herstel originele items in kamer 57 (de 2 deuren)
    await conn.query('DELETE FROM items WHERE room_id = 57');
    await conn.query(`
        INSERT INTO items (user_id, room_id, item_id, wall_pos, x, y, z, rot, extra_data, wired_data, limited_data, guild_id)
        VALUES 
            (2, 57, 10, '', 7, 6, 0, 0, '0', '', '0:0', 0),
            (2, 57, 10, '', 7, 4, 0, 0, '0', '', '0:0', 0)
    `);

    console.log('Alles succesvol teruggezet naar normale Arcturus Habbo staat!');
    await conn.end();
}

revert().catch(console.error);
