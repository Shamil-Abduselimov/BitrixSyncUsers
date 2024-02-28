<?php
define('URL', 'https://site2.ru/api/syncUsers');
define('METHOD', 'POST');

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$users = \Bitrix\Main\UserTable::getList([
    'select' => [
        'userId' => 'ID',
        'userName' => 'NAME',
        'userEmail' => 'EMAIL'
    ],
    'filter' => [
        'ACTIVE' => 'Y',
        'BLOCKED' => 'N',
    ]
])->fetchAll();

$curl = curl_init();
curl_setopt_array(
    $curl,
    [
        CURLOPT_URL => URL_TO_SEND,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => METHOD_TO_SEND,
    ]
);

if (curl_exec($curl) === false) {
    echo 'Error curl: ' . curl_error($curl);
} else {
    echo 'success';
}

curl_close($curl);
