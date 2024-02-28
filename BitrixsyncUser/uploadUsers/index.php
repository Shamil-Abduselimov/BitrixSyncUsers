<?php
define('URL', 'https://site1.ru/api/uploadUsers');
define('METHOD', 'GET');

$curl = curl_init();
curl_setopt_array(
    $curl,
    [
        CURLOPT_URL => URL,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => METHOD,
    ]
);

$response = curl_exec($curl);
if ($response === false) die('Error curl: ' . curl_error($curl));
curl_close($curl);

$users = json_decode($response,true);

echo "<pre>";
print_r($users);
echo "</pre>";

