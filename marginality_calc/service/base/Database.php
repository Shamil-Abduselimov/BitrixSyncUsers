<?php
function Database($deal_id,$id = null) {
    require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/php_interface/dbconn.php"); 
    $mysqli = mysqli_init();
    $mysqli->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, true);
    $mysqli->ssl_set(NULL, NULL, "/home/bitrix/www/.mysql/root.crt", NULL, NULL);
    $mysqli->real_connect($DBHost, $DBLogin, $DBPassword, $DBName);
    $mysqli->set_charset("utf8");
    if ($mysqli->connect_errno) {
        printf("Соединение не удалось: %s\n", $mysqli->connect_error);
        exit();
    }
    $table = 'calc_database';

    $data = [
        "`DEAL_ID` = '".$deal_id."'",
        "`BITRIX_URL` = '".mb_substr($_SERVER['HTTP_ORIGIN'],8)."'",
        $id ? "`ID` = '".$id."'" : null,
    ];

    $data = implode(" AND ",array_filter($data));

    $query = "SELECT * FROM `".$table."` WHERE ".$data." ORDER BY `DATE` DESC";

    $db_result = $mysqli->query($query);
    $result = [];
    foreach ($db_result as $row) {
        $result[] = $row;
    }

    return $result;
}