<?php
require_once('base/Request.php');
require_once('base/Database.php');

function GetDeal($deal_id,$id = null) {
    $info = Database($deal_id,$id);

    $data = Rest('crm.deal.list',[
        'select' => ['ID','TITLE','ASSIGNED_BY_ID'],
        'filter' => [
            'ID' => $deal_id
        ]
    ]);

    return [
        'info' => $info[0],
        'data' => $data[0]
    ];
}