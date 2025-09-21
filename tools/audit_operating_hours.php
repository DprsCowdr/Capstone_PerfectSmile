<?php
require_once __DIR__ . '/../vendor/autoload.php';
if (is_file(__DIR__ . '/../tests/_bootstrap.php')) require_once __DIR__ . '/../tests/_bootstrap.php';

try { $db = \Config\Database::connect(); } catch (Exception $e) { echo "DB connect failed: " . $e->getMessage() . "\n"; exit(1); }

// Attempt to read branches table; handle DB prefix/test environments by trying both 'branches' and prefixed 'db_branches'
$rows = [];
try {
    $rows = $db->table('branches')->select('id, name, operating_hours')->get()->getResultArray();
} catch (\Exception $e) {
    // try with prefixed table name used by test DB
    try {
        $rows = $db->table('db_branches')->select('id, name, operating_hours')->get()->getResultArray();
    } catch (\Exception $e2) {
        echo "Could not read branches table. This may mean the DB schema isn't present in the current environment.\n";
        echo "Message: " . $e2->getMessage() . "\n";
        echo "No audit performed. If you want me to attempt fixes, run this script against a DB with the application schema.\n";
        exit(0);
    }
}
if (empty($rows)) { echo "No branches found.\n"; exit(0); }

$suspects = [];
foreach ($rows as $r) {
    $id = $r['id'];
    $name = $r['name'] ?? '';
    $oh = json_decode($r['operating_hours'], true);
    if (!is_array($oh)) continue;
    foreach ($oh as $day => $info) {
        if (empty($info['enabled'])) continue;
        $open = isset($info['open']) ? $info['open'] : '08:00';
        $close = isset($info['close']) ? $info['close'] : '20:00';
        // validate format
        if (!preg_match('/^([0-9]{1,2}):([0-9]{2})$/', $open, $mo) || !preg_match('/^([0-9]{1,2}):([0-9]{2})$/', $close, $mc)) {
            $suspects[] = ['branch_id'=>$id,'branch_name'=>$name,'day'=>$day,'open'=>$open,'close'=>$close,'issue'=>'invalid_format'];
            continue;
        }
        $openH = (int)$mo[1];
        $openM = (int)$mo[2];
        $closeH = (int)$mc[1];
        $closeM = (int)$mc[2];
        // close earlier than open on same day
        if ($closeH < $openH || ($closeH == $openH && $closeM <= $openM)) {
            $suspects[] = ['branch_id'=>$id,'branch_name'=>$name,'day'=>$day,'open'=>$open,'close'=>$close,'issue'=>'close_not_after_open'];
        }
        // heuristics: close hour <8 while open >=8
        if ($closeH < 8 && $openH >= 8) {
            $suggestedCloseH = $closeH + 12;
            $suggested = str_pad((string)$suggestedCloseH,2,'0',STR_PAD_LEFT) . ':' . str_pad((string)$closeM,2,'0',STR_PAD_LEFT);
            $suspects[] = ['branch_id'=>$id,'branch_name'=>$name,'day'=>$day,'open'=>$open,'close'=>$close,'issue'=>'close_looks_am_but_should_be_pm','suggested'=>$suggested];
        }
    }
}

if (empty($suspects)) {
    echo "No suspicious operating_hours entries found.\n";
    exit(0);
}

echo "Found " . count($suspects) . " suspicious operating_hours entries:\n\n";
foreach ($suspects as $s) {
    echo "Branch ID: {$s['branch_id']} | Name: {$s['branch_name']} | Day: {$s['day']} | open={$s['open']} close={$s['close']} | issue={$s['issue']}";
    if (isset($s['suggested'])) echo " | suggested_close={$s['suggested']}";
    echo "\n";
}

echo "\nNote: This script only reports suspicious rows. To auto-apply fixes (e.g. add 12h to close when appropriate) run a reviewed SQL update.\n";
