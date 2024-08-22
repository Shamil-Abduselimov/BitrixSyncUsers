<?php

use Bitrix\Main\Loader;
use Bitrix\Tasks\TaskTable;
use Bitrix\Main\EventManager;
use Bitrix\Main\SystemException;
use Bitrix\Crm\Timeline\CommentEntry;
use Bitrix\Crm\Timeline\Entity\TimelineTable;
use Bitrix\Crm\Timeline\TimelineManager;
use Bitrix\Crm\Timeline\TimelineType;

Loader::includeModule('crm');
Loader::includeModule('tasks');

EventManager::getInstance()->addEventHandler(
    'crm',
    'OnBeforeCrmDealUpdate',
    'checkTasksBeforeStageChange'
);

function checkTasksBeforeStageChange(&$arFields)
{
    // ID текущей сделки
    $dealId = $arFields['ID'];

    // Проверяем, переводится ли сделка на стадию "Увольнение"
    if ($arFields['STAGE_ID'] === "C74:LOSE") {
        // Получаем все задачи, связанные с этой сделкой
        try {
            $tasks = TaskTable::getList([
                'filter' => ['UF_CRM_TASK' => 'D_' . $dealId],
                'select' => ['ID', 'STATUS']
            ])->fetchAll();

            // Проверяем, закрыты ли все задачи
            $allTasksClosed = true;
            foreach ($tasks as $task) {
                if ($task['STATUS'] !== "5") {
                    $allTasksClosed = false;
                    break;
                }
            }

            // Если не все задачи закрыты, добавляем ошибку
            if (!$allTasksClosed) {
                throw new \Exception('Не все задачи на стадии "КДП" закрыты. Пожалуйста, закройте все задачи перед переводом на стадию "Увольнение".');
            }
        } catch (\Exception $exception) {
            global $APPLICATION;
            $arFields['RESULT_MESSAGE'] = $exception->getMessage();
            $APPLICATION->throwException($arFields['RESULT_MESSAGE']);
            return false;
        }
    }
}