<?php
/**
 * Simple & Powerful Amazon Session Keeper
 * Fast, lightweight, and efficient - now supports multiple users
 */

// Define the project root and include the config file
define('PROJECT_ROOT', realpath(dirname(__FILE__)) . '/../');
include PROJECT_ROOT . 'auth/config.php';
date_default_timezone_set('Asia/Kolkata');
/**
 * FastSessionKeeper class to manage Amazon sessions.
 * * This class uses cURL multi to send parallel requests to keep sessions alive.
 * It now includes methods to handle multiple user sessions from a database.
 */
class FastSessionKeeper {

    private $userAgents = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/120.0.0.0 Safari/537.36',
        'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 Chrome/120.0.0.0 Safari/537.36'
    ];
    
    private $endpoints = [
        'https://www.amazon.in/',
        'https://www.amazon.in/gp/css/homepage.html',
        'https://www.amazon.in/pay/history'
    ];
    
    /**
     * Sends fast parallel requests to Amazon endpoints to keep a session alive.
     *
     * @param array $cookies An associative array of cookies.
     * @return bool True if at least one request is successful, false otherwise.
     */
    public function keepAlive($cookies) {
        if (empty($cookies)) {
            // No echo here, handled by summary later
            return false;
        }
        
        $cookieString = $this->formatCookies($cookies);
        $multiHandle = curl_multi_init();
        $curlHandles = [];
        
        foreach ($this->endpoints as $index => $url) {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 15,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_COOKIE => $cookieString,
                CURLOPT_USERAGENT => $this->userAgents[array_rand($this->userAgents)],
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 3,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_ENCODING => 'gzip',
                CURLOPT_HTTPHEADER => [
                    'Accept: text/html,application/xhtml+xml,*/*;q=0.9',
                    'Accept-Language: en-US,en;q=0.8',
                    'Cache-Control: no-cache',
                    'Connection: keep-alive'
                ]
            ]);
            
            curl_multi_add_handle($multiHandle, $ch);
            $curlHandles[$index] = $ch;
        }
        
        $running = null;
        do {
            curl_multi_exec($multiHandle, $running);
            curl_multi_select($multiHandle);
        } while ($running > 0);
        
        $successCount = 0;
        
        foreach ($curlHandles as $index => $ch) {
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            
            if ($httpCode >= 200 && $httpCode < 400 && empty($error)) {
                $successCount++;
            }
            // No echo here
            
            curl_multi_remove_handle($multiHandle, $ch);
            curl_close($ch);
        }
        
        curl_multi_close($multiHandle);
        
        $success = $successCount > 0;
        // No echo here
        
        return $success;
    }
    
    /**
     * Fetches all active cookies from the database for all users.
     *
     * @return array|null An array of associative arrays, where each inner array
     * represents a user's cookies, or null on failure.
     */
    public function getAllActiveCookies() {
        global $conn;
        
        if ($conn->connect_error) {
            echo "❌ DB Error: " . $conn->connect_error . "<br>";
            return null;
        }
        
        $sql = "SELECT id, ubid_acbin, at_acbin, x_acbin, date FROM amazon_token WHERE status = 'Active'";
        $result = $conn->query($sql);
        
        if ($result) {
            if ($result->num_rows > 0) {
                $sessions = [];
                while ($row = $result->fetch_assoc()) {
                    $sessions[] = [
                        'date' => $row['date'],
                        'id' => $row['id'],
                        'cookies' => [
                            'ubid-acbin' => $row['ubid_acbin'],
                            'at-acbin'   => $row['at_acbin'],
                            'x-acbin'    => $row['x_acbin']
                        ]
                    ];
                }
                return $sessions;
            } else {
                // No echo here, handled by main logic
                return null;
            }
        } else {
            echo "❌ DB Query Error (getAllActiveCookies): " . $conn->error . "<br>";
            return null;
        }
    }
    
    /**
     * Formats an array of cookies into a semicolon-separated string.
     *
     * @param array $cookies Associative array of cookies.
     * @return string The formatted cookie string.
     */
    private function formatCookies($cookies) {
        $parts = [];
        foreach ($cookies as $key => $value) {
            if (!empty($value)) {
                $parts[] = "{$key}={$value}";
            }
        }
        return implode('; ', $parts);
    }
    
    /**
     * Advanced session validation with Amazon-specific checks.
     *
     * @param array $cookies An associative array of cookies.
     * @return bool True if the session appears to be logged in, false otherwise.
     */
    public function validateSession($cookies) {
        if (empty($cookies) || empty($cookies['ubid-acbin'])) {
            return false;
        }
        
        $cookieString = $this->formatCookies($cookies);
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://www.amazon.in/gp/css/homepage.html',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_COOKIE => $cookieString,
            CURLOPT_USERAGENT => $this->userAgents[0],
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => [
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language: en-US,en;q=0.5'
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($response === false || $httpCode !== 200) {
            return false;
        }
        
        // Check for Amazon login indicators
        $loggedIn = strpos($response, 'nav-link-accountList') !== false || 
                            strpos($response, 'Hello') !== false ||
                            strpos($response, 'Your Account') !== false;
        
        return $loggedIn;
    }
    
    /**
     * Simulates user activity on Amazon to keep the session fresh.
     *
     * @param array $cookies An associative array of cookies.
     */
    private function simulateUserActivity($cookies) {
        $cookieString = $this->formatCookies($cookies);
        
        $activities = [
            'https://www.amazon.in/amazonpay/home?ref_=nav_cs_apay',
            'https://www.amazon.in/apay/landing/gc-top-up?ref_=apay_deskhome_card_APayBalance_AddMoney&ingress=apay_deskhome_card',
            'https://www.amazon.in/pay/history'
        ];
        
        $randomActivity = $activities[array_rand($activities)];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $randomActivity,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_COOKIE => $cookieString,
            CURLOPT_USERAGENT => $this->userAgents[array_rand($this->userAgents)],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => [
                'Accept: text/html,application/xhtml+xml,*/*;q=0.9',
                'Referer: https://www.amazon.in/',
                'Cache-Control: no-cache'
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        // No echo here
    }

    /**
     * Keeps a single session super active with multiple strategies.
     *
     * @param array $cookies An associative array of cookies.
     * @return bool True if the session is successfully kept alive, false otherwise.
     */
    public function superKeepAlive($cookies) {
        // No echo here
        
        $result1 = $this->keepAlive($cookies);
        
        if ($result1) {
            $this->simulateUserActivity($cookies);
        }
        
        $valid = $this->validateSession($cookies);
        
        // No echo here
        
        return $result1;
    }
}

// Auto-cleanup function for memory management
function cleanup() {
    if (function_exists('gc_collect_cycles')) {
        gc_collect_cycles();
    }
}

// Main execution - Super fast and efficient
try {
    $startTime = microtime(true);
    
    $keeper = new FastSessionKeeper();
    
    // Get ALL active cookies from the database
    $allSessions = $keeper->getAllActiveCookies();
    
    // Initialize counters for the summary
    $totalSessionsProcessed = 0;
    $totalSessionsSynced = 0;
    $totalSessionsSkipped = 0;
    $totalSessionsFailedToSync = 0; // New counter for sessions that failed to keep alive

    if ($allSessions) {
        $totalSessionsProcessed = count($allSessions);
        
        foreach ($allSessions as $session) {
            $currentTime = time();
            $userId = $session['id'];
            $lastSync = $session['date']; 
            $lastSyncTimestamp = strtotime($lastSync);
            $cookies = $session['cookies'];
            $timeDifferenceMinutes = ($currentTime - $lastSyncTimestamp) / 60;
            // echo($timeDifferenceMinutes."<br>");
            if ($timeDifferenceMinutes > 30) { 
                $success = $keeper->superKeepAlive($cookies);
                
                if ($success) {
                    $sql = "UPDATE `amazon_token` SET `date`=NOW() WHERE `id` = '$userId'";
                    global $conn;
                    $result = $conn->query($sql);
                    
                    if($result && $conn->affected_rows > 0){
                        $totalSessionsSynced++;
                    } else {
                        // This counts sessions where keepAlive was true, but DB update failed or no rows affected
                        $totalSessionsFailedToSync++; 
                    }
                } else {
                    // Session failed to keep alive via cURL, so it wasn't synced
                    $totalSessionsFailedToSync++; 
                }
            } else {
                $totalSessionsSkipped++;
            }
        }
        
        $endTime = microtime(true);
        $executionTime = round(($endTime - $startTime), 2);
        
        // --- SMART SUMMARY ---
        echo "--- Session Sync Report ---<br>";
        echo "⚡ **Total execution time:** {$executionTime}s<br>";
        echo "📊 **Total sessions found:** {$totalSessionsProcessed}<br>";
        echo "✅ **Sessions successfully synced (updated in DB):** {$totalSessionsSynced}<br>";
        echo "⏭️ **Sessions skipped (under 30 mins since last sync):** {$totalSessionsSkipped}<br>";
        
        // This accounts for sessions that failed to keep alive OR failed DB update
        $trulyFailed = $totalSessionsProcessed - $totalSessionsSynced - $totalSessionsSkipped;
        if ($trulyFailed < 0) $trulyFailed = 0; // Prevent negative if logic leads to it
        
        echo "❌ **Sessions failed to sync (or DB update failed):** {$trulyFailed}<br>";
        echo "--------------------------<br>";
        
        cleanup();
        exit($totalSessionsSynced > 0 ? 0 : 1);
    } else {
        echo "❌ No active sessions available to process!<br>";
        exit(1);
    }
    
} catch (Exception $e) {
    echo "💥 Error: " . $e->getMessage() . "<br>";
    cleanup();
    exit(1);
} catch (Error $e) {
    echo "💥 Fatal: " . $e->getMessage() . "<br>";
    cleanup();
    exit(1);
}

?>