<?php
// SMTP Configuration for Sendinblue
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Your Sendinblue API Key
$api_key = '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';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $type = $data['type'] ?? '';
    $email = $data['email'] ?? '';
    $otp = $data['otp'] ?? '';
    $name = $data['name'] ?? '';
    
    if (empty($email) || empty($otp)) {
        echo json_encode(['success' => false, 'message' => 'Email and OTP are required']);
        exit();
    }
    
    // Send email based on type
    switch($type) {
        case 'register':
            $subject = 'Verify Your Account - Jay Prasad Majhi Systems';
            $html_content = generateVerificationEmail($name, $otp);
            break;
            
        case 'login':
            $subject = 'Login Verification Code';
            $html_content = generateLoginOTPEmail($otp);
            break;
            
        case 'reset':
            $subject = 'Password Reset Request';
            $html_content = generateResetEmail($name, $otp);
            break;
            
        default:
            $subject = 'Verification Code';
            $html_content = generateDefaultOTPEmail($otp);
    }
    
    // Send email via Sendinblue
    $response = sendSendinblueEmail($api_key, $email, $subject, $html_content);
    
    echo json_encode($response);
}

function sendSendinblueEmail($api_key, $to, $subject, $html_content) {
    $curl = curl_init();
    
    $post_data = [
        'sender' => [
            'name' => 'Jay Prasad Majhi Systems',
            'email' => 'noreply@jayprasad.com.np'
        ],
        'to' => [[
            'email' => $to,
            'name' => explode('@', $to)[0]
        ]],
        'subject' => $subject,
        'htmlContent' => $html_content,
        'headers' => [
            'X-Mailin-custom' => 'custom_header_1:custom_value_1|custom_header_2:custom_value_2'
        ]
    ];
    
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.sendinblue.com/v3/smtp/email",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode($post_data),
        CURLOPT_HTTPHEADER => [
            "accept: application/json",
            "api-key: " . $api_key,
            "content-type: application/json"
        ],
    ]);
    
    $response = curl_exec($curl);
    $err = curl_error($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    
    curl_close($curl);
    
    if ($err) {
        return ['success' => false, 'message' => 'CURL Error: ' . $err];
    } else {
        $response_data = json_decode($response, true);
        
        if ($http_code >= 200 && $http_code < 300) {
            return ['success' => true, 'message' => 'Email sent successfully', 'message_id' => $response_data['messageId'] ?? ''];
        } else {
            return ['success' => false, 'message' => 'API Error: ' . ($response_data['message'] ?? 'Unknown error'), 'code' => $http_code];
        }
    }
}

function generateVerificationEmail($name, $otp) {
    return '
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; background: #0a0a0f; color: white; padding: 20px; }
            .container { max-width: 600px; margin: 0 auto; background: rgba(20, 20, 30, 0.9); border-radius: 15px; padding: 30px; border: 1px solid #00f3ff; }
            .header { text-align: center; margin-bottom: 30px; }
            .logo { color: #00f3ff; font-size: 24px; font-weight: bold; }
            .otp-box { background: rgba(0, 243, 255, 0.1); border: 2px solid #00f3ff; border-radius: 10px; padding: 20px; text-align: center; margin: 30px 0; font-size: 32px; font-weight: bold; letter-spacing: 10px; }
            .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #8888cc; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <div class="logo">JAY PRASAD MAJHI SYSTEMS</div>
                <div style="color: #00f3ff; font-size: 14px;">Secure Authentication Portal</div>
            </div>
            
            <h2>Welcome, ' . htmlspecialchars($name) . '!</h2>
            
            <p>Thank you for registering with Jay Prasad Majhi Systems. To complete your registration and verify your email address, please use the following One-Time Password (OTP):</p>
            
            <div class="otp-box">' . $otp . '</div>
            
            <p>This OTP is valid for 5 minutes. If you did not request this verification, please ignore this email.</p>
            
            <p>For security reasons:</p>
            <ul>
                <li>Never share this OTP with anyone</li>
                <li>Our team will never ask for your OTP</li>
                <li>Delete this email after verification</li>
            </ul>
            
            <div class="footer">
                <p>© 2024 Jay Prasad Majhi. All rights reserved.</p>
                <p>This email was sent to you as part of our secure authentication process.</p>
            </div>
        </div>
    </body>
    </html>';
}

function generateLoginOTPEmail($otp) {
    return '
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; background: #0a0a0f; color: white; padding: 20px; }
            .container { max-width: 600px; margin: 0 auto; background: rgba(20, 20, 30, 0.9); border-radius: 15px; padding: 30px; border: 1px solid #00f3ff; }
            .header { text-align: center; margin-bottom: 30px; }
            .logo { color: #00f3ff; font-size: 24px; font-weight: bold; }
            .otp-box { background: rgba(0, 243, 255, 0.1); border: 2px solid #00f3ff; border-radius: 10px; padding: 20px; text-align: center; margin: 30px 0; font-size: 32px; font-weight: bold; letter-spacing: 10px; }
            .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #8888cc; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <div class="logo">SECURE LOGIN VERIFICATION</div>
                <div style="color: #00f3ff; font-size: 14px;">Two-Factor Authentication</div>
            </div>
            
            <h2>Login Verification Required</h2>
            
            <p>We detected a login attempt to your account. For security purposes, please verify this action using the following One-Time Password (OTP):</p>
            
            <div class="otp-box">' . $otp . '</div>
            
            <p><strong>Time:</strong> ' . date('Y-m-d H:i:s') . ' (UTC)</p>
            <p><strong>Browser:</strong> Unknown</p>
            <p><strong>Location:</strong> Unknown</p>
            
            <p><strong>If this was you:</strong> Enter the OTP above to complete your login.</p>
            <p><strong>If this was NOT you:</strong> Immediately change your password and contact support.</p>
            
            <div class="footer">
                <p>© 2024 Jay Prasad Majhi Security Systems. All rights reserved.</p>
                <p>This is an automated security message. Do not reply.</p>
            </div>
        </div>
    </body>
    </html>';
}
?>
