<?php

// Ваши данные для авторизации
define('WEBHOOK_URL', 'https://crm.vitamedrf.ru/rest/1859/fkfddp0fbvttvedz/');

// Функция для выполнения запроса к REST API
function callRestMethod($method, $params = [])
{
	$url = WEBHOOK_URL . $method;
	$queryData = http_build_query($params);
	$response = file_get_contents($url . '?' . $queryData);
	return json_decode($response, true);
}

// Функция для поиска пользователя по ФИО и названию компании
function findUserByFIOAndCompany($fio)
{
	$users = callRestMethod('user.search', ['FIND' => explode(' ', $fio)[0] . ' ' . explode(' ', $fio)[1]]);
	if (!empty($users['result'])) {
		foreach ($users['result'] as $user) {
			if ($user['SECOND_NAME'] == explode(' ', $fio)[2])
				return $user;
		}
	}
	return null;
}

// Функция для отправки сообщения в чат
function sendMessageToUser($userId, $message)
{
	$params = [
		'USER_ID' => $userId,
		'MESSAGE' => $message,
	];
	return callRestMethod('im.message.add', $params);
}

// Функция для формирования сообщения
function createMessage($fio, $gender, $companyName)
{
	$salutation = ($gender === 'F') ? 'Уважаемая' : 'Уважаемый';
	return <<<EOT
		$salutation $fio,

		Мы с сожалением восприняли ваше решение покинуть нашу команду. Выражаем искреннюю благодарность за вашу преданность, профессионализм и тёплую атмосферу, которую вы создавали в нашем коллективе.

		В день увольнения, перед уходом, пожалуйста, соберите все вещи, выданные вам компанией. После того, как приедете в офис следуйте инструкции:
		1.    Если у вас есть медицинское оборудование, которое необходимо вернуть, сначала посетите медицинский склад , который находится на первом этаже.
		2.    Затем поднимитесь на третий этаж, комната 304, чтобы сдать пропуск.
		3.    Если у вас есть какое-либо оборудование (например, мобильный телефон или ноутбук), обратитесь в ИТ-отдел, кабинет 305.
		4.    После того как вы всё сдали и мы получили уведомление об этом, вы можете пройти в кабинет 301, где находится отдел кадров, для подписания увольнительных документов.

		Благодарим вас за вклад в нашу команду, который вы приносили каждый день.
		Желаем вам успехов и удачи на новом этапе жизни.

		Помните, что наша дверь всегда открыта для вас, и мы надеемся на возможность встретиться снова в будущем.

		С наилучшими пожеланиями,
		Коллектив $companyName.
	EOT;
}

// Проверяем, что запрос является POST и содержит параметры userFIO и companyName
$fio = $_REQUEST['userFIO']; // ФИО пользователя из POST запроса
$companyName = $_REQUEST['companyName']; // Название компании из POST запроса

$user = findUserByFIOAndCompany($fio);
if ($user) {
	$userId = $user['ID'];
	$gender = $user['PERSONAL_GENDER']; // Получаем значение поля "Пол" (PERSONAL_GENDER)
	$message = createMessage($fio, $gender, $companyName); // Формируем сообщение
	sendMessageToUser($userId, $message);
}