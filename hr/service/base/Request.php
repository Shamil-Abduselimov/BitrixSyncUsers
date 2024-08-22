<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/local/application/hr/src/crest.php");

function Rest($method, $data = []) {
    if(CRest::installApp()) {
        return CRest::call($method,$data);
    }
}
function Curl($method, $data) {
    $webhook = 'https://crm.vitamedrf.ru/rest/10994/qjtvtne5w17t2bvs/';

    if(!$data) return false;

    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => $webhook.$method.'?'.http_build_query($data),
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST'
    ));
    
    $response = curl_exec($curl);
    
    curl_close($curl);

    return $response;
}