import mysql from 'mysql2/promise';

async function updateRoom() {
    const conn = await mysql.createConnection({
        host: '127.0.0.1',
        user: 'root',
        password: '',
        database: 'habbo',
    });

    console.log('Verbinding gemaakt met database habbo');

    // 1. Maak kamer 57 leeg
    await conn.query('DELETE FROM items WHERE room_id = 57');

    // 2. Plaats TBS Cel meubels:
    // Bed (49) op (2, 2)
    // Tafel (17) op (4, 2)
    // Kruk (26) op (4, 3)
    // Smokkelkist (3620) op (2, 5)
    const itemsToAdd = [
        { itemId: 49, x: 2, y: 2, rot: 2 },
        { itemId: 17, x: 4, y: 2, rot: 0 },
        { itemId: 26, x: 4, y: 3, rot: 0 },
        { itemId: 3620, x: 2, y: 5, rot: 0 },
    ];

    for (const item of itemsToAdd) {
        await conn.query(
            'INSERT INTO items (user_id, room_id, item_id, wall_pos, x, y, z, rot, extra_data, wired_data, limited_data, guild_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [2, 57, item.itemId, '', item.x, item.y, 0, item.rot, '0', '', '0:0', 0]
        );
    }

    // 3. Update kamer parameters naar TBS betonnen vloer en stalen celmuren
    await conn.query(
        'UPDATE rooms SET name = ?, description = ?, paper_floor = ?, paper_wall = ? WHERE id = 57',
        ['TBS-Kliniek: Celblok A #104', 'Zwaar beveiligde TBS afdeling met betonnen vloer en stalen tralies', '201', '101']
    );

    // 4. Update Daniel outfit naar TBS Inmate Orange
    await conn.query(
        'UPDATE users SET look = ? WHERE username = ?',
        ['hr-115-42.hd-190-1.ch-210-48.lg-270-48.sh-290-62', 'Daniel']
    );

    console.log('Kamer 57 en Daniel succesvol getransformeerd naar TBS-Kliniek Celblok A!');
    await conn.end();
}

updateRoom().catch(console.error);
