<?php
require_once('base/Database.php');
require_once('GetUser.php');

function GetHistory($deal_id) {
    $result = Database($deal_id);

    foreach ($result as &$value) {
        $value['TITLE'] = 'Расчет от '.date('d.m.Y',strtotime($value['DATE']));
        $value['DATE'] = date('d.m.Y H:i:s',strtotime($value['DATE']));
        $value['ASSIGNED'] = GetUser($value['USER_ID']);
        $value['ALLOWED'] = $value['ALLOW_ID'] ? GetUser($value['ALLOW_ID']) : '';        
    }

    return $result;
}