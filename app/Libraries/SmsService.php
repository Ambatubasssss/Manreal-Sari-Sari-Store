<?php

namespace App\Libraries;

class SmsService
{
    protected $iprogtechApiToken;

    public function __construct()
    {
        $this->iprogtechApiToken = getenv('IPROGTECH_API_TOKEN');
    }

    public function sendSMS($to, $message)
    {
        if (!$this->iprogtechApiToken) {
            log_message('error', 'Iprogtech API token not configured');
            return false;
        }

        $to = $this->normalizePhoneNumber($to);

        $url = 'https://sms.iprogtech.com/api/v1/sms_messages';

        // Format phone number ( Philippine format without + )
        $phoneNumber = ltrim($to, '+'); // Remove + prefix

        $data = [
            'api_token' => $this->iprogtechApiToken,
            'message' => $message,
            'phone_number' => $phoneNumber
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);

        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        log_message('info', 'Iprogtech API Response (' . $statusCode . '): ' . $response);

        // Check for success response
        if ($statusCode == 200 || $statusCode == 201) {
            $result = json_decode($response, true);
            if ($result && !isset($result['error'])) {
                log_message('info', 'Iprogtech SMS sent successfully');
                return true;
            }
        }

        log_message('error', 'Failed to send SMS via Iprogtech API: ' . $response);
        return false;
    }

    protected function normalizePhoneNumber($number)
    {
        $number = preg_replace('/\D/', '', $number);

        if (strlen($number) == 10 && substr($number, 0, 1) == '9') {
            $number = '+63' . $number;
        } elseif (strlen($number) == 11 && substr($number, 0, 2) == '09') {
            $number = '+63' . substr($number, 1);
        } elseif (substr($number, 0, 1) != '+') {
            $number = '+1' . $number;
        }

        return $number;
    }

    public function sendOTP($phoneNumber, $otp)
    {
        $message = "Your OTP verification code for Manreal Store is: {$otp}. Please enter this code to complete your registration.";
        return $this->sendSMS($phoneNumber, $message);
    }
}
