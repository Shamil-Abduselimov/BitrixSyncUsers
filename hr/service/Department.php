<?php
require_once('base/Request.php');

function getDepartments() {
    return Rest('department.get')['result'];
}