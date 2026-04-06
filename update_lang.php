<?php
$files = ['en', 'am', 'om'];

$new = [
    'System' => ['en' => 'System', 'am' => 'ስርዓት', 'om' => 'Sirna'],
    'Overview' => ['en' => 'Overview', 'am' => 'አጠቃላይ እይታ', 'om' => 'Ilaalcha'],
    'Active Terminal' => ['en' => 'Active Terminal', 'am' => 'ገባሪ ተርሚናል', 'om' => 'Tarminaala Hojjechaa Jiru'],
    'Shortages' => ['en' => 'Shortages', 'am' => 'እጥረቶች', 'om' => 'Hirdhina'],
    'Active Floor Map' => ['en' => 'Active Floor Map', 'am' => 'የመመገቢያ ካርታ', 'om' => 'Kaartaa Bakka Nyaataa'],
    'Real-time table status and order management grid.' => ['en' => 'Real-time table status and order management grid.', 'am' => 'የጠረጴዛ ሁኔታ እና የትዕዛዝ አስተዳደር።', 'om' => 'Haala minjaalaa fi bulchiinsa ajajjuu.'],
    'available' => ['en' => 'Available', 'am' => 'ክፍት', 'om' => 'Duwaa'],
    'occupied' => ['en' => 'Occupied', 'am' => 'ተይዟል', 'om' => 'Qabameera'],
    'reserved' => ['en' => 'Reserved', 'am' => 'የተያዘ', 'om' => 'Qabsiifameera'],
    'cleaning' => ['en' => 'Cleaning', 'am' => 'እየጸዳ', 'om' => 'Qulqullaa\'aa Jira'],
    'SECTION' => ['en' => 'SECTION', 'am' => 'ክፍል', 'om' => 'KUTAA'],
    'ORDER' => ['en' => 'ORDER', 'am' => 'ትዕዛዝ', 'om' => 'AJAJA'],
    'Items Selected' => ['en' => 'Items Selected', 'am' => 'ያረፉ ትዕዛዞች', 'om' => 'Meeshaalee Filataman'],
    'NEW ORDER' => ['en' => 'NEW ORDER', 'am' => 'አዲስ ትዕዛዝ', 'om' => 'AJAJA HAARAA'],
    'VIEW' => ['en' => 'VIEW', 'am' => 'እይ', 'om' => 'ILAALI'],
    'ADD' => ['en' => 'ADD', 'am' => 'ጨምር', 'om' => 'IDAA\'I'],
    'CLEARED' => ['en' => 'CLEARED', 'am' => 'ጸድቷል', 'om' => 'QULQULLAA\'EERA'],
    'New Order' => ['en' => 'New Order', 'am' => 'አዲስ ትዕዛዝ', 'om' => 'Ajaja Haaraa'],
    'Table' => ['en' => 'Table', 'am' => 'ጠረጴዛ', 'om' => 'Minjaala'],
    'seats' => ['en' => 'seats', 'am' => 'ወንበሮች', 'om' => 'teessuma'],
    'No available items in this category' => ['en' => 'No available items in this category', 'am' => 'በዚህ ምድብ ምንም የለም', 'om' => 'Ramaddii kana keessa homtuu hin jiru'],
    'Order Cart' => ['en' => 'Order Cart', 'am' => 'የትዕዛዝ ቅርጫት', 'om' => 'Kaartii Ajajaa'],
    'CLEAR' => ['en' => 'CLEAR', 'am' => 'አጽዳ', 'om' => 'HAAXAA\'I'],
    'each' => ['en' => 'each', 'am' => 'ካንዱ', 'om' => 'tokkoon'],
    'Edit note' => ['en' => 'Edit note', 'am' => 'ማስታወሻ አስተካክል', 'om' => 'Yaada sirreessi'],
    'Add note' => ['en' => 'Add note', 'am' => 'ማስታወሻ ጨምር', 'om' => 'Yaada idaa\'i'],
    'Tap items to add them' => ['en' => 'Tap items to add them', 'am' => 'ለመጨመር ይጫኑ', 'om' => 'Idaa\'uuf tuqi'],
    'Your order will appear here' => ['en' => 'Your order will appear here', 'am' => 'ትዕዛዝዎ እዚህ ይታያል', 'om' => 'Ajajni keessan asitti mul\'ata'],
    'Order Notes' => ['en' => 'Order Notes', 'am' => 'የትዕዛዝ ማስታወሻ', 'om' => 'Yaada Ajajaa'],
    'Order Total' => ['en' => 'Order Total', 'am' => 'የክፍያ ድምር', 'om' => 'Cuunfaa Ajajaa'],
    'Place Order' => ['en' => 'Place Order', 'am' => 'ትዕዛዙን ላክ', 'om' => 'Ajaji'],
    'Placing Order...' => ['en' => 'Placing Order...', 'am' => 'ትዕዛዙ በመላክ ላይ...', 'om' => 'Ajajaa Jira...']
];

foreach ($files as $lang) {
    $path = "lang/{$lang}.json";
    $current = file_exists($path) ? json_decode(file_get_contents($path), true) : [];
    foreach ($new as $key => $translations) {
        $current[$key] = $translations[$lang];
    }
    file_put_contents($path, json_encode($current, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}
echo 'SUCCESS';
