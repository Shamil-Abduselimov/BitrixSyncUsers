<?php
header("Access-Control-Allow-Origin: *");
require_once($_SERVER["DOCUMENT_ROOT"]."/local/application/hr/service/base/Request.php");

// Организации
$organization = [
    "crm.vitamedrf.ru" => [
        "id" => 19659,
        "name" => 'ООО "Вита Мед"'
    ],
    "it-doc.bitrix24.ru" => [
        "id" => 19660,
        "name" => 'ООО "АЙ ТИ ДОК"'
    ],
    "crm.go-doc.ru" => [
        "id" => 19661,
        "name" => 'ООО "Медосмотр"'
    ],
    "b24-k1q67q.bitrix24.ru" => [
        "id" => 19662,
        "name" => 'ООО "Предрейсовый осмотр"'
    ],
    "b24-txjv7k.bitrix24.ru" => [
        "id" => 19663,
        "name" => 'ООО "СМП МЕД"'
    ]
];

// Функция для фильтрации пользователей по ключу
function getUsersByKey($users, $key) {
    return array_values(array_filter($users, function ($user) use($key) {
        return $user['ROLE'] == $key;
    }));
}

if ($_POST) {
    $data = [];
    $method = '';

    // Обработка файлов
    if ($_FILES && isset($_FILES['fields'])) {
        foreach ($_FILES['fields']['tmp_name'] as $key => $tmpName) {
            if ($_FILES['fields']['error'][$key] == 0) {
                $_POST['fields'][$key] = [
                    'fileData' => [
                        htmlspecialchars($_FILES['fields']['name'][$key], ENT_QUOTES, 'UTF-8'),
                        base64_encode(file_get_contents($tmpName))
                    ]
                ];
            }
        }
    }

    // Экранирование входных данных
    $entity = htmlspecialchars($_POST['entity'], ENT_QUOTES, 'UTF-8');
    $entityId = htmlspecialchars($_POST['entity_id'], ENT_QUOTES, 'UTF-8');
    $domain = htmlspecialchars($_REQUEST['DOMAIN'], ENT_QUOTES, 'UTF-8');

    switch ($entity) {
        case 'deal':
            $data = [
                'fields' => array_merge([
                    'CATEGORY_ID' => $entityId,
                    'RESPONSIBLE_ID' => 10994,
                    'UF_CRM_1714212535' => $organization[$domain]['id']
                ], $_POST['fields'])
            ];
            $method = 'crm.deal.add';
            break;
        case 'task':
            $group = Rest('sonet_group.user.get', ['ID' => $entityId]);

            $responsible = null;
            $accomplices = null;
            $auditors = null;

            if (!isset($group['error'])) {
                $users = $group['result'];

                $responsible = getUsersByKey($users, 'A')[0]['USER_ID'];
                $accomplices = array_map(function($user) {
                    return $user['USER_ID'];
                }, getUsersByKey($users, 'E'));
                $auditors = array_map(function($user) {
                    return $user['USER_ID'];
                }, getUsersByKey($users, 'K'));
            } else {
                die(json_encode($group));
            }

            $data = [
                'fields' => array_merge([
                    'GROUP_ID' => $entityId,
                    'RESPONSIBLE_ID' => $responsible,
                    'ACCOMPLICES' => $accomplices,
                    'AUDITORS' => $auditors,
                ], $_POST['fields'])
            ];
            $method = 'tasks.task.add';
            break;
    }

    $result = null;

    if ($_SERVER['SERVER_NAME'] !== $domain) {
        $data['fields']['TITLE'] = $organization[$domain]['name'] . ' - ' . htmlspecialchars($data['fields']['TITLE'], ENT_QUOTES, 'UTF-8');
        $result = Curl($method, $data);
    } else {
        $result = Rest($method, $data);
    }

    echo json_encode([$_REQUEST, $result]);
} else {
    header('HTTP/1.1 500 Internal Server Error');
    header('Content-Type: application/json; charset=UTF-8');
    die(json_encode(['message' => 'ERROR', 'code' => 1337]));
}
?>