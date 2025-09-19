<?php
echo 'Testing day of week calculations:' . PHP_EOL;
echo 'Current date: ' . date('Y-m-d l w') . PHP_EOL;
echo PHP_EOL;

// Test dates for this week
$dates = [
    '2024-12-15', // Sunday
    '2024-12-16', // Monday  
    '2024-12-17', // Tuesday
    '2024-12-18', // Wednesday
    '2024-12-19', // Thursday
    '2024-12-20', // Friday
    '2024-12-21'  // Saturday
];

foreach ($dates as $date) {
    $dow = date('w', strtotime($date));
    $dayName = date('l', strtotime($date));
    echo $date . ' is ' . $dayName . ' (dow=' . $dow . ')' . PHP_EOL;
}

echo PHP_EOL . 'Testing Monday matching:' . PHP_EOL;
$mapping = ['sun'=>0,'mon'=>1,'tue'=>2,'wed'=>3,'thu'=>4,'fri'=>5,'sat'=>6];
$targetDow = $mapping['mon']; // Should be 1
echo 'Target DOW for Monday: ' . $targetDow . PHP_EOL;

foreach ($dates as $date) {
    $actualDow = (int)date('w', strtotime($date));
    $matches = ($actualDow === $targetDow);
    echo $date . ' (dow=' . $actualDow . ') matches Monday(' . $targetDow . '): ' . ($matches ? 'YES' : 'NO') . PHP_EOL;
}

echo PHP_EOL . 'Testing sample recurring logic:' . PHP_EOL;
// Simulate the actual logic from getBlocksBetween
$start = '2024-12-15';
$end = '2024-12-21';
$startTs = strtotime($start);
$endTs = strtotime($end);

echo 'Range: ' . $start . ' to ' . $end . PHP_EOL;
echo 'StartTs: ' . $startTs . ' (' . date('Y-m-d H:i:s', $startTs) . ')' . PHP_EOL;
echo 'EndTs: ' . $endTs . ' (' . date('Y-m-d H:i:s', $endTs) . ')' . PHP_EOL;
echo PHP_EOL;

// Test the iteration logic
$targetDow = 1; // Monday
echo 'Looking for day of week: ' . $targetDow . ' (Monday)' . PHP_EOL;

$cur = strtotime(date('Y-m-d', $startTs));
$count = 0;
while ($cur <= $endTs && $count < 10) { // safety counter
    $curDow = (int)date('w', $cur);
    $curDate = date('Y-m-d', $cur);
    $isMatch = ($curDow === $targetDow);
    echo 'Date: ' . $curDate . ' DOW: ' . $curDow . ' Match: ' . ($isMatch ? 'YES' : 'NO') . PHP_EOL;
    
    if ($isMatch) {
        echo '  -> Would create event on ' . $curDate . PHP_EOL;
    }
    
    $cur = strtotime('+1 day', $cur);
    $count++;
}
?>