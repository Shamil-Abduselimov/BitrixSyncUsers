<?php
require_once('base/Request.php');

function IsAdmin() {
    $result = Rest('user.admin',[]);
    if($result['error']) {
        die('Ошибка авторизации пользователя:<br><pre>'.print_r($result).'</pre>');
    }
    return $result;
}
