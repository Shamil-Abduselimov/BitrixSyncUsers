<?
header("Access-Control-Allow-Origin: *");

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/php_interface/dbconn.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/local/application/marginality_calc/service/base/Request.php");
$mysqli = mysqli_init();
$mysqli->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, true);
$mysqli->ssl_set(NULL, NULL, "/home/bitrix/www/.mysql/root.crt", NULL, NULL);
$mysqli->real_connect($DBHost, $DBLogin, $DBPassword, $DBName);
$mysqli->set_charset("utf8");
$table = 'calc_database';
$result = null;

if ($_POST) {
    $_POST['info'] = json_decode($_POST['info'], true);
    $config = json_decode(file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/local/application/marginality_calc/config.json"), true)[$_POST['info']['bitrix']];

    switch ($_POST['info']['action']) {
        case 'send':
            if ($_POST['info']['status'] == 'allow') {
                $data = [
                    'CALC_DATA' => $_POST['data'],
                    'STATUS' => $_POST['info']['action']
                ];

                $result = sendNotice($config['ROP'], 'Прошу утвердить доработки в расчете по сделке.');

                $sql = "UPDATE `" . $table . "` SET " . updateValues($data) . " WHERE `ID` = '" . $_POST['info']['deal_info']['info']['ID'] . "'";
            } else {
                $data = [
                    "BITRIX_URL" => $_POST['info']['bitrix'],
                    "DEAL_ID" => $_POST['info']['deal_info']['data']['ID'],
                    "USER_ID" => $_POST['info']['user_info']['ID'],
                    "CALC_DATA" => $_POST['data'],
                    "STATUS" => $_POST['info']['action'],
                ];

                $sql = "INSERT INTO `" . $table . "` " . insertValues($data);

                $result = sendNotice($config['ROP'], 'Прошу утвердить расчет по сделке');
            }
            break;
        case 'allow':
            $data = [
                'ALLOW_ID' => $_POST['info']['user_info']['ID'],
                "CALC_DATA" => $_POST['data'],
                'STATUS' => $_POST['info']['action']
            ];

            $result = sendNotice($_POST['info']['deal_info']['info']['USER_ID'], 'Вам выдано разрешение на доработку расчета по сделке.');

            $sql = "UPDATE `" . $table . "` SET " . updateValues($data) . " WHERE `ID` = '" . $_POST['info']['deal_info']['info']['ID'] . "'";
            break;
        case 'save':
            if ($_POST['info']['deal_info']['info'] && $_POST['info']['status'] !== 'save') {
                $data = [
                    'STATUS' => $_POST['info']['action']
                ];

                $result = sendNotice($_POST['info']['deal_info']['info']['USER_ID'], 'Ваш расчет утвержден руководителем ОП.');

                $sql = "UPDATE `" . $table . "` SET " . updateValues($data) . " WHERE `ID` = '" . $_POST['info']['deal_info']['info']['ID'] . "'";
            } else {
                $data = [
                    "BITRIX_URL" => $_POST['info']['bitrix'],
                    "DEAL_ID" => $_POST['info']['deal_info']['data']['ID'],
                    "USER_ID" => $_POST['info']['user_info']['ID'],
                    "CALC_DATA" => $_POST['data'],
                    "STATUS" => $_POST['info']['action'],
                ];

                $result = sendNotice($_POST['info']['deal_info']['info']['USER_ID'], 'Новый расчет успешно сохранен.');

                $sql = "INSERT INTO `" . $table . "` " . insertValues($data);
            }
            break;
    }

    $deal = updateCalcStatus();
    $mysqli->query($sql);

    echo json_encode([$mysqli->insert_id, $data, $result, $deal]);
}

function updateValues($data)
{
    foreach ($data as $key => &$value) {
        $value = "`" . $key . "`='" . $value . "'";
    }
    return implode(', ', $data);
}
function insertValues($data)
{
    $keys = implode(", ", array_map(function ($key) {
        return "`" . $key . "`";
    }, array_keys($data)));
    $values = implode(", ", array_map(function ($value) {
        return "'" . $value . "'";
    }, array_values($data)));

    return implode(' VALUES ', ["(" . $keys . ")", "(" . $values . ")"]);
}
function sendNotice($user_id, $message)
{
    $data = [
        'USER_ID' => $user_id,
        'MESSAGE' => 'Калькулятор маржинальности',
        'ATTACH' => [
            [
                'LINK' => [
                    'PREVIEW' => 'https://crm.vitamedrf.ru//local/application/marginality_calc/img/calc_icon.png',
                    'WIDTH' => '90',
                    'HEIGHT' => '90',
                    'NAME' => 'Сделка: ' . $_POST['info']['deal_info']['data']['TITLE'],
                    'DESC' => $message,
                    'LINK' => 'https://' . $_POST['info']['bitrix'] . '/crm/deal/details/' . $_POST['info']['deal_info']['data']['ID'] . '/?active_tab=tab_rest_736'
                ]
            ],
            [
                "DELIMITER" => [
                    'SIZE' => 200,
                    'COLOR' => "#c6c6c6"
                ]
            ]
        ]
    ];
    return Rest('im.notify.personal.add', $data);
}
function updateCalcStatus()
{
    $config = json_decode(file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/local/application/marginality_calc/config.json"), true)[$_POST['info']['bitrix']];
    $data = [
        $config['CALC_STATUS_FIELD']['ID'] => $config['CALC_STATUS_FIELD']['VALUE'][$_POST['info']['action']]
    ];

    if ($_POST['info']['action'] == 'save')
        $data['OPPORTUNITY'] = json_decode($_POST['data'], true)['Goods']['total'];

    $fields = [
        'id' => $_POST['info']['deal_info']['data']['ID'],
        'fields' => $data,
        'params' => [
            "REGISTER_SONET_EVENT" => "Y"
        ]
    ];

    return Rest('crm.deal.update', $fields);
}
?>