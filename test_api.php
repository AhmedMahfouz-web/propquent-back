<?php

/**
 * Simple API Test Script
 * Run this script to test the authentication API endpoints
 */

// Test data
$testUser = [
    'full_name' => 'Test User',
    'email' => 'test@example.com',
    'password' => 'password123',
    'password_confirmation' => 'password123',
    'country' => 'United States',
    'phone_number' => '+1234567890'
];

$baseUrl = 'http://127.0.0.1:8000/api';

echo "=== PropQuent API Test Script ===\n\n";

// Function to make HTTP requests
function makeRequest($url, $method = 'GET', $data = null, $headers = []) {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            $headers[] = 'Content-Type: application/json';
        }
    }
    
    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return ['error' => $error, 'http_code' => 0];
    }
    
    return [
        'response' => json_decode($response, true),
        'http_code' => $httpCode,
        'raw_response' => $response
    ];
}

// Test 1: Register User
echo "1. Testing User Registration...\n";
$result = makeRequest($baseUrl . '/auth/register', 'POST', $testUser);

if ($result['http_code'] === 201) {
    echo "✅ Registration successful!\n";
    $token = $result['response']['data']['token'] ?? null;
    echo "   Token: " . substr($token, 0, 20) . "...\n";
} else {
    echo "❌ Registration failed!\n";
    echo "   HTTP Code: " . $result['http_code'] . "\n";
    echo "   Response: " . ($result['raw_response'] ?? 'No response') . "\n";
}

echo "\n";

// Test 2: Login User
echo "2. Testing User Login...\n";
$loginData = [
    'email' => $testUser['email'],
    'password' => $testUser['password']
];

$result = makeRequest($baseUrl . '/auth/login', 'POST', $loginData);

if ($result['http_code'] === 200) {
    echo "✅ Login successful!\n";
    $token = $result['response']['data']['token'] ?? null;
    echo "   Token: " . substr($token, 0, 20) . "...\n";
} else {
    echo "❌ Login failed!\n";
    echo "   HTTP Code: " . $result['http_code'] . "\n";
    echo "   Response: " . ($result['raw_response'] ?? 'No response') . "\n";
}

echo "\n";

// Test 3: Logout (if we have a token)
if (isset($token) && $token) {
    echo "3. Testing Logout (Token Blacklisting)...\n";
    $headers = ['Authorization: Bearer ' . $token];
    $result = makeRequest($baseUrl . '/auth/logout', 'POST', null, $headers);
    
    if ($result['http_code'] === 200) {
        echo "✅ Logout successful - token blacklisted!\n";
        echo "   Message: " . ($result['response']['message'] ?? 'N/A') . "\n";
        
        // Test if token is actually blacklisted by trying to use it again
        echo "\n3b. Testing if token is blacklisted...\n";
        $testResult = makeRequest($baseUrl . '/auth/logout', 'POST', null, $headers);
        if ($testResult['http_code'] === 401) {
            echo "✅ Token successfully blacklisted - cannot be reused!\n";
        } else {
            echo "❌ Token not properly blacklisted!\n";
        }
    } else {
        echo "❌ Logout failed!\n";
        echo "   HTTP Code: " . $result['http_code'] . "\n";
        echo "   Response: " . ($result['raw_response'] ?? 'No response') . "\n";
    }
    
    echo "\n";
}

// Test 4: Forgot Password
echo "4. Testing Forgot Password...\n";
$forgotData = ['email' => $testUser['email']];
$result = makeRequest($baseUrl . '/auth/forgot-password', 'POST', $forgotData);

if ($result['http_code'] === 200) {
    echo "✅ Forgot password successful!\n";
    $resetToken = $result['response']['data']['reset_token'] ?? null;
    if ($resetToken) {
        echo "   Reset Token: " . substr($resetToken, 0, 20) . "...\n";
        
        // Test 5: Reset Password
        echo "\n5. Testing Reset Password...\n";
        $resetData = [
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123'
        ];
        
        $result = makeRequest($baseUrl . '/auth/reset-password/' . $resetToken, 'POST', $resetData);
        
        if ($result['http_code'] === 200) {
            echo "✅ Reset password successful!\n";
        } else {
            echo "❌ Reset password failed!\n";
            echo "   HTTP Code: " . $result['http_code'] . "\n";
            echo "   Response: " . ($result['raw_response'] ?? 'No response') . "\n";
        }
    }
} else {
    echo "❌ Forgot password failed!\n";
    echo "   HTTP Code: " . $result['http_code'] . "\n";
    echo "   Response: " . ($result['raw_response'] ?? 'No response') . "\n";
}

// Test 6: Home Dashboard (if we have a fresh token from login)
if (isset($token) && $token && !isset($testResult)) {
    echo "6. Testing Home Dashboard...\n";
    $headers = ['Authorization: Bearer ' . $token];
    $result = makeRequest($baseUrl . '/home/dashboard?per_page=5', 'GET', null, $headers);
    
    if ($result['http_code'] === 200) {
        echo "✅ Home dashboard successful!\n";
        $data = $result['response']['data'] ?? null;
        if ($data) {
            echo "   User: " . ($data['user']['full_name'] ?? 'N/A') . "\n";
            echo "   Capital Investment: $" . ($data['financial_summary']['capital_investment']['amount'] ?? '0') . "\n";
            echo "   Total Profit: $" . ($data['financial_summary']['profit']['total'] ?? '0') . "\n";
            echo "   ROI: " . ($data['financial_summary']['roi']['percentage'] ?? '0') . "%\n";
            echo "   Recent Transactions: " . count($data['recent_transactions']['data'] ?? []) . " items\n";
        }
    } else {
        echo "❌ Home dashboard failed!\n";
        echo "   HTTP Code: " . $result['http_code'] . "\n";
        echo "   Response: " . ($result['raw_response'] ?? 'No response') . "\n";
    }
    
    echo "\n";
}

// Test 7: Projects List (if we have a fresh token from login)
if (isset($token) && $token && !isset($testResult)) {
    echo "7. Testing Projects List...\n";
    $headers = ['Authorization: Bearer ' . $token];
    $result = makeRequest($baseUrl . '/projects-list', 'GET', null, $headers);
    
    if ($result['http_code'] === 200) {
        echo "✅ Projects list successful!\n";
        $data = $result['response']['data'] ?? null;
        if ($data) {
            echo "   User Equity: " . ($data['user']['equity_percentage'] ?? '0') . "%\n";
            echo "   Total Asset Value: $" . ($data['financial_summary']['total_asset_value'] ?? '0') . "\n";
            echo "   Non-Exited Projects Amount: $" . ($data['financial_summary']['total_non_exited_projects_amount'] ?? '0') . "\n";
            echo "   Projects Count: " . ($data['projects_count'] ?? '0') . "\n";
            if (!empty($data['projects'])) {
                $firstProject = $data['projects'][0];
                echo "   First Project: " . ($firstProject['title'] ?? 'N/A') . "\n";
                echo "   User Investment in Project: $" . ($firstProject['financial_data']['user_invested_amount'] ?? '0') . "\n";
            }
        }
    } else {
        echo "❌ Projects list failed!\n";
        echo "   HTTP Code: " . $result['http_code'] . "\n";
        echo "   Response: " . ($result['raw_response'] ?? 'No response') . "\n";
    }
    
    echo "\n";
}

// Test 8: User Profile (if we have a fresh token from login)
if (isset($token) && $token && !isset($testResult)) {
    echo "8. Testing User Profile...\n";
    $headers = ['Authorization: Bearer ' . $token];
    $result = makeRequest($baseUrl . '/profile?per_page=5', 'GET', null, $headers);
    
    if ($result['http_code'] === 200) {
        echo "✅ User profile successful!\n";
        $data = $result['response']['data'] ?? null;
        if ($data) {
            echo "   User: " . ($data['user']['full_name'] ?? 'N/A') . " (" . ($data['user']['custom_id'] ?? 'N/A') . ")\n";
            echo "   Email: " . ($data['user']['email'] ?? 'N/A') . "\n";
            echo "   Deposits: $" . ($data['financial_metrics']['deposits'] ?? '0') . "\n";
            echo "   Withdrawals: $" . ($data['financial_metrics']['withdrawals'] ?? '0') . "\n";
            echo "   Equity: $" . ($data['financial_metrics']['equity'] ?? '0') . "\n";
            echo "   Equity %: " . ($data['financial_metrics']['equity_percentage'] ?? '0') . "%\n";
            echo "   Total Profit: $" . ($data['financial_metrics']['total_profit'] ?? '0') . "\n";
            echo "   Recent Transactions: " . count($data['recent_transactions']['data'] ?? []) . " items\n";
        }
    } else {
        echo "❌ User profile failed!\n";
        echo "   HTTP Code: " . $result['http_code'] . "\n";
        echo "   Response: " . ($result['raw_response'] ?? 'No response') . "\n";
    }
    
    echo "\n";
}

echo "\n=== Test Complete ===\n";
echo "Note: Make sure your Laravel development server is running on http://127.0.0.1:8000\n";
echo "Run: php artisan serve --host=127.0.0.1 --port=8000\n";
