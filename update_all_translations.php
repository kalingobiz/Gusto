<?php
$files = ['en', 'am', 'om'];
$new = [
    'Stock Status' => ['en' => 'Stock Status', 'am' => 'የክምችት ሁኔታ', 'om' => 'Haala Kuusaa'],
    'Ingredient Component' => ['en' => 'Ingredient Component', 'am' => 'የግብዓት አካል', 'om' => 'Qaama Meeshaa'],
    'Current Supply' => ['en' => 'Current Supply', 'am' => 'የአሁኑ አቅርቦት', 'om' => 'Dhiyeessii Ammaa'],
    'Unit Cost' => ['en' => 'Unit Cost', 'am' => 'የአሃድ ዋጋ', 'om' => 'Gatii Tokkoo'],
    'Actions' => ['en' => 'Actions', 'am' => 'ተግባራት', 'om' => 'Gochaalee'],
    'Low Stock' => ['en' => 'Low Stock', 'am' => 'ዝቅተኛ ክምችት', 'om' => 'Kuusaa Xiqqaa'],
    'Sufficient' => ['en' => 'Sufficient', 'am' => 'በቂ', 'om' => 'Gahaa'],
    'Measure' => ['en' => 'Measure', 'am' => 'መለኪያ', 'om' => 'Safartuu'],
    'Reorder at' => ['en' => 'Reorder at', 'am' => 'በዚህ መጠን ይዘዙ', 'om' => 'Yeroo kanatti ajajuu'],
    'INTAKE' => ['en' => 'INTAKE', 'am' => 'አስገባ', 'om' => 'GALCHUU'],
    'ISSUE' => ['en' => 'ISSUE', 'am' => 'አውጣ', 'om' => 'BASUU'],
    'ADJUST' => ['en' => 'ADJUST', 'am' => 'አስተካክል', 'om' => 'SIRREESSUU'],
    'LEDGER' => ['en' => 'LEDGER', 'am' => 'መዝገብ', 'om' => 'GALAABA'],
    'Showing' => ['en' => 'Showing', 'am' => 'እያሳየ ነው', 'om' => 'Agarsiisaa jira'],
    'to' => ['en' => 'to', 'am' => 'እስከ', 'om' => 'hanga'],
    'of' => ['en' => 'of', 'am' => 'ከ', 'om' => 'keessaa'],
    'ingredients' => ['en' => 'ingredients', 'am' => 'ግብዓቶች', 'om' => 'meeshaalee'],
    'New Ingredient' => ['en' => 'New Ingredient', 'am' => 'አዲስ ግብዓት', 'om' => 'Meesshaa Haaraya'],
    'Register a raw material in the inventory supply.' => ['en' => 'Register a raw material in the inventory supply.', 'am' => 'አዲስ ጥሬ እቃ በክምችት ውስጥ ይመዝገቡ።', 'om' => 'Meeshaa dheedhii kuusaa keessatti galmeessi.'],
    'Name' => ['en' => 'Name', 'am' => 'ስም', 'om' => 'Maqaa'],
    'Unit' => ['en' => 'Unit', 'am' => 'ዩኒት', 'om' => 'Yuunitii'],
    'Opening Stock' => ['en' => 'Opening Stock', 'am' => 'የመጀመሪያ ክምችት', 'om' => 'Kuusaa Baninsaa'],
    'Min Level' => ['en' => 'Min Level', 'am' => 'ዝቅተኛው መጠን', 'om' => 'Sadarkaa Xiqqaa'],
    'Cost/Unit' => ['en' => 'Cost/Unit', 'am' => 'ዋጋ/ዩኒት', 'om' => 'Gatii/Yuunitii'],
    'Register Entry' => ['en' => 'Register Entry', 'am' => 'ምዝገባ አጠናቅ', 'om' => 'Galmee Xumuri'],
    'Record Stock Intake' => ['en' => 'Record Stock Intake', 'am' => 'የክምችት ግባትን መዝግብ', 'om' => 'Galcha Kuusaa Galmeessi'],
    'Restocking' => ['en' => 'Restocking', 'am' => 'ክምችት እየተተካ ነው', 'om' => 'Kuusaa Bakka Buusaa'],
    'Qty to Add' => ['en' => 'Qty to Add', 'am' => 'የሚጨመር መጠን', 'om' => 'Baay\'ina Dabalamu'],
    'Cost' => ['en' => 'Cost', 'am' => 'ዋጋ', 'om' => 'Gatii'],
    'Supplier' => ['en' => 'Supplier', 'am' => 'አቅራቢ', 'om' => 'Dhiyeessaa'],
    'Ref / Invoice #' => ['en' => 'Ref / Invoice #', 'am' => 'መጠቀሻ/ኢንቮይስ ቁጥር', 'om' => 'Lakkoofsa Invooyisii'],
    'Confirm Intake' => ['en' => 'Confirm Intake', 'am' => 'ግኝትን አረጋግጥ', 'om' => 'Galcha Mirkanessi'],
    'Manual Adjustment' => ['en' => 'Manual Adjustment', 'am' => 'በእጅ ማስተካከያ', 'om' => 'Sirreeffama Harkaa'],
    'Item' => ['en' => 'Item', 'am' => 'እቃ', 'om' => 'Meesshaa'],
    'Qty (– for loss)' => ['en' => 'Qty (– for loss)', 'am' => 'መጠን (ለጉድለት – ምልክት ይጠቀሙ)', 'om' => 'Baay\'ina (hir\'inaaf – fayyadami)'],
    'Reason' => ['en' => 'Reason', 'am' => 'ምክንያት', 'om' => 'Sababa'],
    'Spoilage' => ['en' => 'Spoilage', 'am' => 'ብልሽት', 'om' => 'Baduu'],
    'Damage / Breakage' => ['en' => 'Damage / Breakage', 'am' => 'ስብራት/ጉዳት', 'om' => 'Madaa\'uu / Cabuu'],
    'Staff Meal' => ['en' => 'Staff Meal', 'am' => 'የሰራተኛ ምግብ', 'om' => 'Nyaata Hojjetaa'],
    'Found / Audit' => ['en' => 'Found / Audit', 'am' => 'ኦዲት/የተገኘ', 'om' => 'Argamuu / Ooditii'],
    'Other' => ['en' => 'Other', 'am' => 'ሌላ', 'om' => 'Kan biroo'],
    'Explanation for adjustment' => ['en' => 'Explanation for adjustment', 'am' => 'ለማስተካከያው ማብራሪያ', 'om' => 'Ibsa sirreeffamaa'],
    'Issue to Kitchen' => ['en' => 'Issue to Kitchen', 'am' => 'ለማድቤት ስጥ', 'om' => 'Kushiinaaf kenni'],
    'Amt to Issue' => ['en' => 'Amt to Issue', 'am' => 'የሚሰጥ መጠን', 'om' => 'Baay\'ina kennamu'],
    'Prep Usage' => ['en' => 'Prep Usage', 'am' => 'ለዝግጅት ጥቅም', 'om' => 'Fayyadama Qophiif'],
    'Event Catering' => ['en' => 'Event Catering', 'am' => 'ለክስተት ዝግጅት', 'om' => 'Nyaata Sirnaa'],
    'Confirm Issue' => ['en' => 'Confirm Issue', 'am' => 'መስጠትን አረጋግጥ', 'om' => 'Kennuu Mirkanessi'],
    'Active Floor' => ['en' => 'Active Floor', 'am' => 'ንቁ ወለል', 'om' => 'Bakka Hojii'],
    'Critical Stock' => ['en' => 'Critical Stock', 'am' => 'አሳሳቢ ክምችት', 'om' => 'Kuusaa Hatattamaa'],
    'Review Stock' => ['en' => 'Review Stock', 'am' => 'ክምችት ተመልከት', 'om' => 'Kuusaa Ilaali'],
    'ingredients below reorder level — immediate restock recommended.' => ['en' => 'ingredients below reorder level — immediate restock recommended.', 'am' => 'ግብዓቶች ከዝቅተኛው መጠን በታች ናቸው — በአስቸኳይ እንዲተኩ ይመከራሉ።', 'om' => 'meeshaaleen sadarkaa ajajaa gadi jiru — hatattamaan guutuun ni gorfama.'],
    'Pending' => ['en' => 'Pending', 'am' => 'በጥበቃ ላይ', 'om' => 'Eegamaa jira'],
    'Start Cooking' => ['en' => 'Start Cooking', 'am' => 'ባብስ ጀምር', 'om' => 'Nyaata bilcheessuu jalqabi'],
    'Cooking' => ['en' => 'Cooking', 'am' => 'እየተባበሰ ነው', 'om' => 'Bilchaachaa jira'],
    'Mark Ready' => ['en' => 'Mark Ready', 'am' => 'ደረሰ በል', 'om' => 'Qophii dha jedhi'],
    'Delivered ✓' => ['en' => 'Delivered ✓', 'am' => 'ተረክቧል ✓', 'om' => 'Kennameera ✓'],
    'System Day' => ['en' => 'System Day', 'am' => 'ቀን', 'om' => 'Guyyaa'],
    'System Night' => ['en' => 'System Night', 'am' => 'ማታ', 'om' => 'Halkan'],
    'Executive Overview' => ['en' => 'Executive Overview', 'am' => 'የአመራር አጠቃላይ እይታ', 'om' => 'Ilaalcha Guddicha'],
    'Real-time performance metrics and operational pulse.' => ['en' => 'Real-time performance metrics and operational pulse.', 'am' => 'የአሰራር አፈጻጸም መለኪያዎች።', 'om' => 'Madaallii raawwii hojii ammaa.'],
    'System Live' => ['en' => 'System Live', 'am' => 'ስርዓቱ እየሰራ ነው', 'om' => 'Sirnichu hojira jira'],
    'Record Adjustment' => ['en' => 'Record Adjustment', 'am' => 'ማስተካከያውን መዝግብ', 'om' => 'Sirreeffama Galmeessi'],
    'Confirm & Apply' => ['en' => 'Confirm & Apply', 'am' => 'አረጋግጥ እና ተግብር', 'om' => 'Mirkaneessi & Fayyadami'],
];

foreach ($files as $file) {
    $path = 'lang/' . $file . '.json';
    if (!file_exists($path)) {
        file_put_contents($path, '{}');
    }
    $current = json_decode(file_get_contents($path), true) ?: [];
    foreach ($new as $key => $translations) {
        if (isset($translations[$file])) {
            $current[$key] = $translations[$file];
        }
    }
    file_put_contents($path, json_encode($current, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}
echo "Dictionaries updated successfully.\n";
