<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AvailabilityModel;

class DebugAvailability extends BaseController
{
    public function index()
    {
        try {
            $db = \Config\Database::connect();
            
            $output = "<h1>Availability Debug Info</h1>";
            
            // Check table existence
            if (!$db->tableExists('availability')) {
                $output .= "<p style='color:red'>❌ Availability table does not exist!</p>";
                return $output;
            }
            
            $output .= "<p style='color:green'>✓ Availability table exists</p>";
            
            // Show table structure
            $fields = $db->getFieldData('availability');
            $output .= "<h3>Table Structure:</h3><ul>";
            foreach ($fields as $field) {
                $output .= "<li>{$field->name} ({$field->type})</li>";
            }
            $output .= "</ul>";
            
            // Show recent records
            $query = $db->table('availability')
                       ->orderBy('id', 'DESC')
                       ->limit(20)
                       ->get();
            
            $rows = $query->getResultArray();
            
            $output .= "<h3>Recent Records (" . count($rows) . " found):</h3>";
            
            if (empty($rows)) {
                $output .= "<p>No records found in availability table.</p>";
            } else {
                $output .= "<table border='1' style='border-collapse:collapse'>";
                $output .= "<tr><th>ID</th><th>User</th><th>Type</th><th>Recurring</th><th>Start</th><th>End</th><th>Day</th><th>Time</th><th>Notes</th><th>Created</th></tr>";
                
                foreach ($rows as $row) {
                    $output .= sprintf(
                        "<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s-%s</td><td>%s</td><td>%s</td></tr>",
                        $row['id'],
                        $row['user_id'],
                        $row['type'] ?? 'NULL',
                        $row['is_recurring'] ? 'YES' : 'NO',
                        $row['start_datetime'] ?? 'NULL',
                        $row['end_datetime'] ?? 'NULL',
                        $row['day_of_week'] ?? 'NULL',
                        $row['start_time'] ?? 'NULL',
                        $row['end_time'] ?? 'NULL',
                        htmlspecialchars(substr($row['notes'] ?? '', 0, 30)),
                        $row['created_at'] ?? 'NULL'
                    );
                }
                $output .= "</table>";
            }
            
            // Test create functionality
            $output .= "<h3>Test Create Functionality:</h3>";
            
            $model = new AvailabilityModel();
            
            try {
                // Test ad-hoc creation
                $testId = $model->createBlock([
                    'user_id' => 999,
                    'type' => 'test_debug',
                    'start_datetime' => '2025-09-16 14:00:00',
                    'end_datetime' => '2025-09-16 15:00:00',
                    'notes' => 'Debug test block',
                    'created_by' => 1
                ]);
                
                if ($testId) {
                    $output .= "<p style='color:green'>✓ Test createBlock() successful (ID: $testId)</p>";
                    
                    // Clean up
                    $model->delete($testId);
                    $output .= "<p>✓ Test record cleaned up</p>";
                } else {
                    $output .= "<p style='color:red'>❌ Test createBlock() failed</p>";
                }
                
            } catch (\Exception $e) {
                $output .= "<p style='color:red'>❌ Create test error: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
            
            return $output;
            
        } catch (\Exception $e) {
            return "<h1>Error</h1><p style='color:red'>" . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
    
    public function manualTest()
    {
        // Simulate dentist authentication
        session()->set('user', [
            'id' => 26,
            'user_type' => 'dentist',
            'name' => 'Test Dentist'
        ]);
        
        $output = "<h1>Manual Availability Test Results</h1>";
        
        try {
            // Test 1: Direct model creation (ad-hoc)
            $output .= "<h3>Test 1: Direct Model Ad-hoc Creation</h3>";
            $model = new AvailabilityModel();
            
            $testData = [
                'user_id' => 26,
                'type' => 'day_off',
                'start_datetime' => '2025-09-17 10:00:00',
                'end_datetime' => '2025-09-17 16:00:00',
                'notes' => 'Manual test ad-hoc block',
                'created_by' => 26
            ];
            
            $adHocId = $model->createBlock($testData);
            
            if ($adHocId) {
                $output .= "<p style='color:green'>✓ Ad-hoc creation successful (ID: $adHocId)</p>";
            } else {
                $output .= "<p style='color:red'>❌ Ad-hoc creation failed</p>";
            }
            
            // Test 2: Direct model creation (recurring)
            $output .= "<h3>Test 2: Direct Model Recurring Creation</h3>";
            
            $recurringData = [
                'user_id' => 26,
                'type' => 'recurring',
                'day_of_week' => 'Wednesday',
                'start_time' => '08:00',
                'end_time' => '17:00',
                'is_recurring' => 1,
                'notes' => 'Manual test recurring hours',
                'created_by' => 26
            ];
            
            $recurringId = $model->insert($recurringData);
            
            if ($recurringId) {
                $output .= "<p style='color:green'>✓ Recurring creation successful (ID: $recurringId)</p>";
            } else {
                $output .= "<p style='color:red'>❌ Recurring creation failed</p>";
            }
            
            // Test 3: Controller endpoint simulation
            $output .= "<h3>Test 3: Controller Endpoint Simulation</h3>";
            
            $availController = new \App\Controllers\Availability();
            
            // Simulate POST data for ad-hoc
            $_POST = [
                'user_id' => 26,
                'type' => 'sick_leave',
                'start' => '2025-09-18 09:00:00',
                'end' => '2025-09-18 17:00:00',
                'notes' => 'Controller test block',
                csrf_token() => csrf_hash()
            ];
            
            $response = $availController->create();
            $responseBody = $response->getBody();
            
            if (strpos($responseBody, '"success":true') !== false) {
                $output .= "<p style='color:green'>✓ Controller create() successful</p>";
                $output .= "<p>Response: " . htmlspecialchars($responseBody) . "</p>";
            } else {
                $output .= "<p style='color:red'>❌ Controller create() failed</p>";
                $output .= "<p>Response: " . htmlspecialchars($responseBody) . "</p>";
            }
            
            // Test 4: Check what's in the database now
            $output .= "<h3>Test 4: Current Database State</h3>";
            
            $db = \Config\Database::connect();
            $query = $db->table('availability')
                       ->where('user_id', 26)
                       ->orderBy('id', 'DESC')
                       ->limit(5)
                       ->get();
            
            $rows = $query->getResultArray();
            
            if (!empty($rows)) {
                $output .= "<p style='color:green'>Found " . count($rows) . " records for user 26:</p>";
                $output .= "<table border='1'>";
                $output .= "<tr><th>ID</th><th>Type</th><th>Recurring</th><th>Start</th><th>End</th><th>Day</th><th>Notes</th></tr>";
                
                foreach ($rows as $row) {
                    $output .= sprintf(
                        "<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>",
                        $row['id'],
                        $row['type'],
                        $row['is_recurring'] ? 'YES' : 'NO',
                        $row['start_datetime'] ?? $row['start_time'],
                        $row['end_datetime'] ?? $row['end_time'],
                        $row['day_of_week'] ?? '-',
                        htmlspecialchars($row['notes'] ?? '')
                    );
                }
                $output .= "</table>";
            } else {
                $output .= "<p style='color:orange'>No records found for user 26</p>";
            }
            
        } catch (\Exception $e) {
            $output .= "<p style='color:red'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
            $output .= "<p>File: " . $e->getFile() . "</p>";
            $output .= "<p>Line: " . $e->getLine() . "</p>";
        }
        
        return $output;
    }
    
    public function testCreate()
    {
        // Simulate a dentist user session
        session()->set('user', [
            'id' => 26,
            'user_type' => 'dentist',
            'name' => 'Debug Dentist'
        ]);
        
        // Get CSRF token
        $token = csrf_hash();
        
        $output = "<h1>Test Availability Creation</h1>";
        
        // Create test form
        $output .= "<h3>Test Ad-hoc Creation:</h3>";
        $output .= "<form method='POST' action='/dentist/availability/create'>";
        $output .= "<input type='hidden' name='" . csrf_token() . "' value='$token'>";
        $output .= "<input type='hidden' name='user_id' value='26'>";
        $output .= "Type: <select name='type'><option value='day_off'>Day Off</option><option value='emergency'>Emergency</option></select><br>";
        $output .= "Start: <input type='datetime-local' name='start' value='2025-09-17T09:00'><br>";
        $output .= "End: <input type='datetime-local' name='end' value='2025-09-17T17:00'><br>";
        $output .= "Notes: <input type='text' name='notes' value='Debug test creation'><br>";
        $output .= "<input type='submit' value='Create Ad-hoc Block'>";
        $output .= "</form>";
        
        $output .= "<h3>Test Recurring Creation:</h3>";
        $output .= "<form method='POST' action='/dentist/availability/createRecurring'>";
        $output .= "<input type='hidden' name='" . csrf_token() . "' value='$token'>";
        $output .= "<input type='hidden' name='user_id' value='26'>";
        $output .= "Day: <select name='day_of_week'><option value='Monday'>Monday</option><option value='Tuesday'>Tuesday</option></select><br>";
        $output .= "Start Time: <input type='time' name='start_time' value='09:00'><br>";
        $output .= "End Time: <input type='time' name='end_time' value='17:00'><br>";
        $output .= "Notes: <input type='text' name='notes' value='Debug recurring hours'><br>";
        $output .= "<input type='submit' value='Create Recurring Hours'>";
        $output .= "</form>";
        
        return $output;
    }

    public function directDbTest()
    {
        echo "<h2>Direct Database Test</h2>";
        
        try {
            // Test direct database insert without models
            $db = \Config\Database::connect();
            
            echo "<h3>1. Test Raw Database Insert</h3>";
            $rawData = [
                'user_id' => 1,
                'type' => 'test_direct',
                'start_datetime' => '2024-01-15 09:00:00',
                'end_datetime' => '2024-01-15 17:00:00',
                'is_recurring' => 0,
                'notes' => 'Direct DB test ' . date('Y-m-d H:i:s'),
                'created_by' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            echo "<p>Inserting data: " . json_encode($rawData) . "</p>";
            
            $builder = $db->table('availability');
            $result = $builder->insert($rawData);
            $insertId = $db->insertID();
            
            echo "<p>Raw insert result: " . ($result ? 'SUCCESS' : 'FAILED') . "</p>";
            echo "<p>Insert ID: $insertId</p>";
            
            if ($insertId) {
                $verify = $builder->where('id', $insertId)->get()->getRowArray();
                echo "<p>Verification query: " . json_encode($verify) . "</p>";
            }
            
            echo "<h3>2. Test Model Insert</h3>";
            $model = new \App\Models\AvailabilityModel();
            
            $modelData = [
                'user_id' => 1,
                'type' => 'test_model',
                'start_datetime' => '2024-01-15 10:00:00',
                'end_datetime' => '2024-01-15 18:00:00',
                'notes' => 'Model test ' . date('Y-m-d H:i:s'),
                'created_by' => 1
            ];
            
            echo "<p>Model data: " . json_encode($modelData) . "</p>";
            
            $modelResult = $model->createBlock($modelData);
            echo "<p>Model result: " . json_encode($modelResult) . "</p>";
            echo "<p>Model insert ID: " . $model->getInsertID() . "</p>";
            
            echo "<h3>3. Test Recurring Insert</h3>";
            $recurringData = [
                'user_id' => 1,
                'day_of_week' => 'Monday',
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'type' => 'recurring',
                'is_recurring' => 1,
                'notes' => 'Recurring test ' . date('Y-m-d H:i:s'),
                'created_by' => 1
            ];
            
            echo "<p>Recurring data: " . json_encode($recurringData) . "</p>";
            
            $recurringResult = $model->insert($recurringData);
            echo "<p>Recurring result: " . json_encode($recurringResult) . "</p>";
            echo "<p>Recurring insert ID: " . $model->getInsertID() . "</p>";
            
            echo "<h3>4. Current Table Contents (last 10 rows)</h3>";
            $recent = $builder->orderBy('id', 'DESC')->limit(10)->get()->getResultArray();
            echo "<table border='1'>";
            echo "<tr><th>ID</th><th>User ID</th><th>Type</th><th>Start</th><th>End</th><th>Day</th><th>Recurring</th><th>Notes</th><th>Created</th></tr>";
            foreach ($recent as $row) {
                echo "<tr>";
                echo "<td>" . $row['id'] . "</td>";
                echo "<td>" . $row['user_id'] . "</td>";
                echo "<td>" . $row['type'] . "</td>";
                echo "<td>" . ($row['start_datetime'] ?? 'N/A') . "</td>";
                echo "<td>" . ($row['end_datetime'] ?? 'N/A') . "</td>";
                echo "<td>" . ($row['day_of_week'] ?? 'N/A') . "</td>";
                echo "<td>" . $row['is_recurring'] . "</td>";
                echo "<td>" . ($row['notes'] ?? 'N/A') . "</td>";
                echo "<td>" . $row['created_at'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            
        } catch (\Exception $e) {
            echo "<p style='color: red;'>Exception: " . $e->getMessage() . "</p>";
            echo "<pre>" . $e->getTraceAsString() . "</pre>";
        }
    }

    public function mondayDebug()
    {
        echo "<h2>Monday Recurring Blocks Debug</h2>";
        
        try {
            $db = \Config\Database::connect();
            
            echo "<h3>1. All Recurring Records in Database</h3>";
            $recurringRecords = $db->table('availability')
                                  ->where('is_recurring', 1)
                                  ->get()
                                  ->getResultArray();
            
            echo "<p>Found " . count($recurringRecords) . " recurring records:</p>";
            echo "<table border='1'>";
            echo "<tr><th>ID</th><th>User ID</th><th>Day</th><th>Start Time</th><th>End Time</th><th>Type</th><th>Notes</th><th>Created</th></tr>";
            foreach ($recurringRecords as $row) {
                echo "<tr>";
                echo "<td>" . $row['id'] . "</td>";
                echo "<td>" . $row['user_id'] . "</td>";
                echo "<td>" . $row['day_of_week'] . "</td>";
                echo "<td>" . $row['start_time'] . "</td>";
                echo "<td>" . $row['end_time'] . "</td>";
                echo "<td>" . $row['type'] . "</td>";
                echo "<td>" . ($row['notes'] ?? '') . "</td>";
                echo "<td>" . $row['created_at'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            echo "<h3>2. Test Recurring Expansion for Current Week</h3>";
            $model = new \App\Models\AvailabilityModel();
            
            // Get current week range
            $today = new DateTime();
            $monday = clone $today;
            $monday->modify('monday this week');
            $sunday = clone $monday;
            $sunday->modify('+6 days');
            
            $startDate = $monday->format('Y-m-d');
            $endDate = $sunday->format('Y-m-d 23:59:59');
            
            echo "<p>Testing range: $startDate to $endDate</p>";
            
            $expandedEvents = $model->getBlocksBetween($startDate, $endDate);
            
            echo "<p>Found " . count($expandedEvents) . " expanded events:</p>";
            echo "<table border='1'>";
            echo "<tr><th>ID</th><th>User ID</th><th>Type</th><th>Start DateTime</th><th>End DateTime</th><th>Is Recurring</th><th>Notes</th></tr>";
            foreach ($expandedEvents as $event) {
                echo "<tr>";
                echo "<td>" . $event['id'] . "</td>";
                echo "<td>" . $event['user_id'] . "</td>";
                echo "<td>" . ($event['type'] ?? '') . "</td>";
                echo "<td>" . ($event['start_datetime'] ?? '') . "</td>";
                echo "<td>" . ($event['end_datetime'] ?? '') . "</td>";
                echo "<td>" . $event['is_recurring'] . "</td>";
                echo "<td>" . ($event['notes'] ?? '') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            echo "<h3>3. Manual Day-of-Week Testing</h3>";
            $testDates = [];
            for ($i = 0; $i < 7; $i++) {
                $testDate = clone $monday;
                $testDate->modify("+$i days");
                $testDates[] = [
                    'date' => $testDate->format('Y-m-d'),
                    'day_name' => $testDate->format('l'),
                    'day_num' => $testDate->format('w')
                ];
            }
            
            echo "<table border='1'>";
            echo "<tr><th>Date</th><th>Day Name</th><th>Day Number (0=Sun)</th><th>Should Show Monday Events?</th></tr>";
            foreach ($testDates as $date) {
                $shouldShow = $date['day_num'] == 1 ? 'YES' : 'NO';
                $color = $date['day_num'] == 1 ? 'green' : 'red';
                echo "<tr>";
                echo "<td>" . $date['date'] . "</td>";
                echo "<td>" . $date['day_name'] . "</td>";
                echo "<td>" . $date['day_num'] . "</td>";
                echo "<td style='color: $color; font-weight: bold;'>" . $shouldShow . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            
        } catch (\Exception $e) {
            echo "<p style='color: red;'>Exception: " . $e->getMessage() . "</p>";
            echo "<pre>" . $e->getTraceAsString() . "</pre>";
        }
    }

    public function calendarEvents()
    {
        $start = $this->request->getGet('start') ?? '2024-12-15';
        $end = $this->request->getGet('end') ?? '2024-12-21';
        $dentistId = $this->request->getGet('dentist_id');

        echo "<h1>Calendar Events Debug</h1>";
        echo "<p>This shows what the /calendar/availability-events endpoint returns</p>";
        echo "<p><strong>Parameters:</strong> start=$start, end=$end" . ($dentistId ? ", dentist_id=$dentistId" : "") . "</p>";

        try {
            $model = new \App\Models\AvailabilityModel();
            $blocks = $model->getBlocksBetween($start, $end, $dentistId ?: null);

            echo "<h2>Raw Blocks from getBlocksBetween():</h2>";
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>ID</th><th>User ID</th><th>Type</th><th>Start</th><th>End</th><th>Is Recurring</th><th>Notes</th></tr>";
            foreach ($blocks as $block) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($block['id']) . "</td>";
                echo "<td>" . htmlspecialchars($block['user_id']) . "</td>";
                echo "<td>" . htmlspecialchars($block['type']) . "</td>";
                echo "<td>" . htmlspecialchars($block['start_datetime']) . "</td>";
                echo "<td>" . htmlspecialchars($block['end_datetime']) . "</td>";
                echo "<td>" . htmlspecialchars($block['is_recurring'] ?? 'N/A') . "</td>";
                echo "<td>" . htmlspecialchars($block['notes'] ?? '') . "</td>";
                echo "</tr>";
            }
            echo "</table>";

            // Map to frontend-friendly events (same as Availability::events)
            $events = array_map(function($b){
                return [
                    'id' => $b['id'],
                    'title' => ucfirst(str_replace('_',' ',$b['type'])),
                    'type' => $b['type'],
                    'start' => $b['start_datetime'],
                    'end' => $b['end_datetime'],
                    'allDay' => false,
                    'user_id' => $b['user_id'],
                    'notes' => $b['notes'] ?? '',
                    'is_recurring' => $b['is_recurring'] ?? 0
                ];
            }, $blocks);

            echo "<h2>Formatted Events (as sent to calendar):</h2>";
            echo "<pre>" . json_encode(['success'=>true,'events'=>$events], JSON_PRETTY_PRINT) . "</pre>";

        } catch (\Exception $e) {
            echo "<p style='color: red;'>Exception: " . $e->getMessage() . "</p>";
            echo "<pre>" . $e->getTraceAsString() . "</pre>";
        }
    }
}