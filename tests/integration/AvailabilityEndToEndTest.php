<?php

/**
 * End-to-End Availability Testing
 * Tests the complete flow from frontend form submission to database storage
 */

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

class AvailabilityEndToEndTest 
{
    private $baseUrl = 'http://localhost:8080';
    
    public function runAllTests()
    {
        echo "<h1>Availability End-to-End Smoke Tests</h1>";
        
        $this->testAdHocAvailabilityFlow();
        $this->testRecurringAvailabilityFlow();
        $this->testCalendarDataFlow();
        $this->testCurrentDatabaseState();
    }
    
    public function testAdHocAvailabilityFlow()
    {
        echo "<h2>Test 1: Ad-hoc Availability Creation Flow</h2>";
        
        try {
            // Step 1: Test direct endpoint call (simulating AJAX)
            echo "<h3>1a. Testing direct endpoint call</h3>";
            
            $postData = [
                'user_id' => 1,
                'type' => 'emergency',
                'start' => '2025-01-15 09:00:00',
                'end' => '2025-01-15 17:00:00',
                'notes' => 'E2E test ad-hoc'
            ];
            
            $response = $this->makePostRequest('/dentist/availability/create', $postData);
            echo "<p>Response Status: " . $response['status'] . "</p>";
            echo "<p>Response Body: " . htmlspecialchars($response['body']) . "</p>";
            
            if ($response['status'] == 200 && strpos($response['body'], '"success":true') !== false) {
                echo "<p style='color:green'>✓ Direct endpoint call successful</p>";
            } else {
                echo "<p style='color:red'>❌ Direct endpoint call failed</p>";
            }
            
            // Step 2: Verify database storage
            echo "<h3>1b. Verifying database storage</h3>";
            $this->verifyDatabaseRecord('emergency', 'E2E test ad-hoc');
            
        } catch (Exception $e) {
            echo "<p style='color:red'>Exception in ad-hoc test: " . $e->getMessage() . "</p>";
        }
    }
    
    public function testRecurringAvailabilityFlow()
    {
        echo "<h2>Test 2: Recurring Availability Creation Flow</h2>";
        
        try {
            // Step 1: Test direct endpoint call
            echo "<h3>2a. Testing direct endpoint call</h3>";
            
            $postData = [
                'user_id' => 1,
                'day_of_week' => 'Tuesday',
                'start_time' => '10:00:00',
                'end_time' => '18:00:00',
                'notes' => 'E2E test recurring'
            ];
            
            $response = $this->makePostRequest('/dentist/availability/createRecurring', $postData);
            echo "<p>Response Status: " . $response['status'] . "</p>";
            echo "<p>Response Body: " . htmlspecialchars($response['body']) . "</p>";
            
            if ($response['status'] == 200 && strpos($response['body'], '"success":true') !== false) {
                echo "<p style='color:green'>✓ Direct endpoint call successful</p>";
            } else {
                echo "<p style='color:red'>❌ Direct endpoint call failed</p>";
            }
            
            // Step 2: Verify database storage
            echo "<h3>2b. Verifying database storage</h3>";
            $this->verifyDatabaseRecord('recurring', 'E2E test recurring');
            
        } catch (Exception $e) {
            echo "<p style='color:red'>Exception in recurring test: " . $e->getMessage() . "</p>";
        }
    }
    
    public function testCalendarDataFlow()
    {
        echo "<h2>Test 3: Calendar Data Retrieval Flow</h2>";
        
        try {
            // Test the events endpoint that the calendar uses
            $eventsUrl = '/dentist/availability/events?start=2025-01-01&end=2025-01-31&user_id=1';
            
            $response = $this->makeGetRequest($eventsUrl);
            echo "<p>Events Response Status: " . $response['status'] . "</p>";
            
            if ($response['status'] == 200) {
                $data = json_decode($response['body'], true);
                if (is_array($data)) {
                    echo "<p style='color:green'>✓ Calendar events endpoint working</p>";
                    echo "<p>Found " . count($data) . " events</p>";
                    
                    // Show first few events
                    foreach (array_slice($data, 0, 3) as $event) {
                        echo "<p>Event: " . htmlspecialchars(json_encode($event)) . "</p>";
                    }
                } else {
                    echo "<p style='color:red'>❌ Calendar events returned invalid JSON</p>";
                }
            } else {
                echo "<p style='color:red'>❌ Calendar events endpoint failed</p>";
                echo "<p>Response: " . htmlspecialchars($response['body']) . "</p>";
            }
            
        } catch (Exception $e) {
            echo "<p style='color:red'>Exception in calendar test: " . $e->getMessage() . "</p>";
        }
    }
    
    public function testCurrentDatabaseState()
    {
        echo "<h2>Test 4: Current Database State</h2>";
        
        try {
            // Connect directly to database
            $config = new \Config\Database();
            $db = \Config\Database::connect();
            
            $query = $db->table('availability')
                       ->orderBy('id', 'DESC')
                       ->limit(10)
                       ->get();
            
            $rows = $query->getResultArray();
            
            echo "<p>Found " . count($rows) . " recent availability records:</p>";
            
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr><th>ID</th><th>User</th><th>Type</th><th>Start DT</th><th>End DT</th><th>Day</th><th>Start Time</th><th>End Time</th><th>Recurring</th><th>Notes</th><th>Created</th></tr>";
            
            foreach ($rows as $row) {
                echo "<tr>";
                echo "<td>" . $row['id'] . "</td>";
                echo "<td>" . $row['user_id'] . "</td>";
                echo "<td>" . ($row['type'] ?? '') . "</td>";
                echo "<td>" . ($row['start_datetime'] ?? '') . "</td>";
                echo "<td>" . ($row['end_datetime'] ?? '') . "</td>";
                echo "<td>" . ($row['day_of_week'] ?? '') . "</td>";
                echo "<td>" . ($row['start_time'] ?? '') . "</td>";
                echo "<td>" . ($row['end_time'] ?? '') . "</td>";
                echo "<td>" . $row['is_recurring'] . "</td>";
                echo "<td>" . ($row['notes'] ?? '') . "</td>";
                echo "<td>" . $row['created_at'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            
        } catch (Exception $e) {
            echo "<p style='color:red'>Exception checking database: " . $e->getMessage() . "</p>";
        }
    }
    
    private function makePostRequest($endpoint, $data)
    {
        $url = $this->baseUrl . $endpoint;
        
        // Create context for POST request
        $postData = http_build_query($data);
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
                'content' => $postData
            ]
        ]);
        
        $response = file_get_contents($url, false, $context);
        $status = $this->getHttpResponseCode($http_response_header);
        
        return [
            'status' => $status,
            'body' => $response
        ];
    }
    
    private function makeGetRequest($endpoint)
    {
        $url = $this->baseUrl . $endpoint;
        
        $response = file_get_contents($url);
        $status = $this->getHttpResponseCode($http_response_header);
        
        return [
            'status' => $status,
            'body' => $response
        ];
    }
    
    private function getHttpResponseCode($headers)
    {
        if (isset($headers[0])) {
            $statusLine = $headers[0];
            if (preg_match('/HTTP\/\d\.\d\s+(\d+)/', $statusLine, $matches)) {
                return (int)$matches[1];
            }
        }
        return 0;
    }
    
    private function verifyDatabaseRecord($type, $notes)
    {
        try {
            $db = \Config\Database::connect();
            $query = $db->table('availability')
                       ->where('type', $type)
                       ->where('notes', $notes)
                       ->orderBy('id', 'DESC')
                       ->limit(1)
                       ->get();
            
            $row = $query->getRowArray();
            
            if ($row) {
                echo "<p style='color:green'>✓ Database record found: ID " . $row['id'] . "</p>";
                echo "<p>Record: " . json_encode($row) . "</p>";
            } else {
                echo "<p style='color:red'>❌ Database record not found for type '$type' with notes '$notes'</p>";
            }
            
        } catch (Exception $e) {
            echo "<p style='color:red'>Database verification failed: " . $e->getMessage() . "</p>";
        }
    }
}

// If this file is accessed directly via web browser, run the tests
if (php_sapi_name() !== 'cli') {
    $tester = new AvailabilityEndToEndTest();
    $tester->runAllTests();
}