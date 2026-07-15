<?php
// Simple Twilio send via REST. Set env vars: TW_SID, TW_TOKEN, TW_FROM
function send_sms_twilio($to, $message) {
    $sid = getenv('TW_SID');
    $token = getenv('TW_TOKEN');
    $from = getenv('TW_FROM'); // e.g. +1234567890

    if (!$sid || !$token || !$from) {
        error_log("Twilio credentials not set");
        return false;
    }

    $url = "https://api.twilio.com/2010-04-01/Accounts/$sid/Messages.json";
    $data = http_build_query([
        'From' => $from,
        'To' => $to,
        'Body' => $message,
    ]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_USERPWD, "$sid:$token");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $resp = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) {
        error_log("Twilio curl error: $err");
        return false;
    }
    return json_decode($resp, true);
}
