<?php
function GetTarif() {
    $data = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'].'/local/application/marginality_calc/config.json'),true)[$_REQUEST['DOMAIN']];
    return $data;
}