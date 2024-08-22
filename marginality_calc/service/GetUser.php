<?php
require_once('base/Request.php');

function GetUser($user_id = null) {
    $data = [
        'ID' => $user_id
    ];

    if(!$user_id) {
        return Rest('user.current',[]);
    } else {
        return Rest('user.get',$data)[0];
    }
}