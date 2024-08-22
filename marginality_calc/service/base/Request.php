<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/local/application/marginality_calc/src/crest.php");
function Rest($method, $data = []) {
    if(CRest::installApp()) {
        $result = CRest::call($method,$data);

        if($result['error']) return $result;
        
        return $result['result'];
    }
}