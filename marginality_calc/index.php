<?php
foreach (glob($_SERVER["DOCUMENT_ROOT"] . "/local/application/marginality_calc/service/*.php") as $filename) {
    require_once($filename);
}

$domain = htmlspecialchars($_REQUEST['DOMAIN'], ENT_QUOTES, 'UTF-8');
$config = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/local/application/marginality_calc/config.json'), true)[$domain];

$placementOptions = json_decode($_REQUEST["PLACEMENT_OPTIONS"], true);
$dealId = htmlspecialchars($placementOptions["ID"], ENT_QUOTES, 'UTF-8');

$deal_info = GetDeal($dealId);
$history_info = GetHistory($dealId);
$user_info = GetUser();
$is_admin = IsAdmin() || $user_info['ID'] == $config["ROP"] ? true : false;
$status = htmlspecialchars($deal_info["info"]["STATUS"], ENT_QUOTES, 'UTF-8');
$comments = json_decode($deal_info['info']['CALC_DATA'], true)['Comment']['comment-value'];
$tarif = GetTarif();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Калькулятор Маржинальности</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="<?= "css/style.css?" . filemtime(__DIR__ . "/css/style.css") ?>" rel="stylesheet">
    <link href="<?= "css/page.css?" . filemtime(__DIR__ . "/css/page.css") ?>" rel="stylesheet">
    <link href="<?= "css/history.css?" . filemtime(__DIR__ . "/css/history.css") ?>" rel="stylesheet">
    <link href="<?= "css/loader.css?" . filemtime(__DIR__ . "/css/loader.css") ?>" rel="stylesheet">

    <script src="//api.bitrix24.com/api/v1/"></script>
    <script src="<?= "js/additional.js?" . filemtime(__DIR__ . "/js/additional.js") ?>"></script>
    <script src="<?= "js/page.js?" . filemtime(__DIR__ . "/js/page.js") ?>"></script>
    <script src="<?= "js/calc.js?" . filemtime(__DIR__ . "/js/calc.js") ?>"></script>
    <script src="<?= "js/comment.js?" . filemtime(__DIR__ . "/js/comment.js") ?>"></script>
</head>

<body>
    <input type="hidden" id="history-info" value="<?= htmlspecialchars(json_encode($history_info), ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" id="deal-info" value="<?= htmlspecialchars(json_encode($deal_info), ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" id="user-info" value="<?= htmlspecialchars(json_encode($user_info), ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" id="tarif" value="<?= htmlspecialchars(json_encode($tarif), ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" id="domain" value="<?= $domain ?>">
    <input type="hidden" id="comment" value="<?= htmlspecialchars(json_encode($comments), ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" id="status" value="<?= $status ? $status : "new" ?>">
    <input type="hidden" id="is-admin" value="<?= $is_admin ? 'true' : 'false' ?>">

    <div class="container">
        <div class="container_block" data-block="option">
            <div class="block_content">
                <div class="content_item">
                    <button data-action="comment" data-cnt="<?= $comments ? count($comments) : '0' ?>">Комментарии</button>
                </div>
                <?php if ($is_admin): ?>
                    <div class="content_item">
                        <button data-action="history">История</button>
                        <script src="<?= "js/history.js?" . filemtime(__DIR__ . "/js/history.js") ?>"></script>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="container_block" data-block="tarif">
            <div class="block_content">
                <div class="content_item">
                    <span class="item--title">Район</span>
                    <select class="item--field" id="tarif-1">
                        <?php foreach ($tarif['TARIF'] as $key => $value): ?>
                            <option value="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>" <?= $key < 1 ? 'selected' : '' ?>>
                                <?= htmlspecialchars($value['place'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="content_item">
                    <span class="item--title">График работы</span>
                    <select class="item--field" id="tarif-2">
                        <option value="206">5/2</option>
                        <option value="250">6/1</option>
                        <option value="304">7/0</option>
                    </select>
                </div>
                <div class="content_item">
                    <span class="item--title">Кол-во часов</span>
                    <select class="item--field" id="tarif-3">
                        <option value="1">1 час</option>
                        <option value="1-5">1.5 часа</option>
                        <option value="2">2 часа</option>
                        <option value="3">3 часа</option>
                        <option value="1.1">1 час утром/1 час вечером</option>
                        <option value="2.2">2 часа утром/2 часа вечером</option>
                        <option value="3.3">3 часа утром/3 часа вечером</option>
                        <option value="8">8 часов</option>
                        <option value="12">12 часов</option>
                        <option value="24">24 часа</option>
                    </select>
                </div>
                <div class="content_item">
                    <span class="item--title">Кол-во мед. персонала (период)</span>
                    <input type="number" class="item--field" id="tarif-4" value="1" min="1">
                </div>
                <div class="content_item">
                    <span class="item--title">Кол-во мед. персонала (присутствие)</span>
                    <input type="number" class="item--field" id="tarif-5" value="1" min="1">
                </div>
            </div>
        </div>
        <div class="container_block" data-block="coeff">
            <div class="block_header">
                <h1 class="block--title">Коэффициент</h1>
            </div>
            <div class="block_content">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Наименование</th>
                            <th>Сумма</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="elem_check">
                                <input type="checkbox" name="" id="coeff-1">
                            </td>
                            <td class="elem_title">От 1 до 2 км от остановки метро</td>
                            <td class="elem_total">2000</td>
                        </tr>
                        <tr>
                            <td class="elem_check">
                                <input type="checkbox" name="" id="coeff-2">
                            </td>
                            <td class="elem_title">Сложное время (с 00-00 до 6-30)</td>
                            <td class="elem_total">3000</td>
                        </tr>
                        <tr>
                            <td class="elem_check">
                                <input type="checkbox" name="" id="coeff-3">
                            </td>
                            <td class="elem_title">От 1 до 2 км пешком от остановки ОТ</td>
                            <td class="elem_total">2000</td>
                        </tr>
                        <tr>
                            <td class="elem_check">
                                <input type="checkbox" name="" id="coeff-4">
                            </td>
                            <td class="elem_title">От 2 км и более пешком от остановки ОТ</td>
                            <td class="elem_total">5000</td>
                        </tr>
                        <tr>
                            <td class="elem_check">
                                <input type="checkbox" name="" id="coeff-5">
                            </td>
                            <td class="elem_title">Срочная вакансия</td>
                            <td class="elem_total">3000</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="2">Итого</th>
                            <th>
                                <span class="table_total" data-result="coeff--total">0</span>
                                <span class="table_unit"> руб.</span>
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <div class="container_block" data-block="fot">
            <div class="block_header">
                <h1 class="block--title">ЗП и налог</h1>
                <select name="" id="fot-option">
                    <option value="td">ТД</option>
                    <option value="gph">ГПХ</option>
                    <option value="other">Иное</option>
                </select>
            </div>
            <div class="block_content">
                <div class="content_item" data-field="stavka">
                    <div class="item--title">Ставка</div>
                    <div class="item--value"></div>
                </div>
                <div class="content_item" data-field="zarplata">
                    <div class="item--title">Зарплата</div>
                    <div class="item--value"></div>
                </div>
                <div class="content_item" data-field="otpusk">
                    <div class="item--title">Отпуск</div>
                    <div class="item--option">
                        <input type="checkbox" checked id="fot-otpusk">
                    </div>
                    <div class="item--value"></div>
                </div>
                <div class="content_item" data-field="accident">
                    <div class="item--title">Страховка от НС (0.2%)</div>
                    <div class="item--value"></div>
                </div>
                <div class="content_item" data-field="fot">
                    <div class="item--title">ФОТ</div>
                    <div class="item--value"></div>
                </div>
                <div class="content_item" data-field="total">
                    <div class="item--title">Итог + Коэффициент</div>
                    <div class="item--value"></div>
                </div>
            </div>
        </div>
        <div class="container_block" data-block="parts">
            <div class="block_header">
                <h1 class="block--title">Расходный материал</h1>
                <div class="header_option">
                    <span class="option--title">Кол-во водителей</span>
                    <input class="option--value" id="staff-count" type="number" value="1" min="1">
                </div>
            </div>
            <div class="block_content">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Наименование</th>
                            <th>Кол-во</th>
                            <th>Цена за ед.</th>
                            <th>Сумма</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tarif['PARTS'] as $part): ?>
                            <tr>
                                <td class="elem_check">
                                    <input type="checkbox" <?= $part['checked'] ? 'checked' : '' ?>>
                                </td>
                                <td class="elem_title">
                                    <?= htmlspecialchars($part['name'], ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td class="elem_count">
                                    <input type="number" id="" value="1" min="1" <?= !$part['input'] ? 'disabled' : '' ?>>
                                </td>
                                <td class="elem_price">
                                    <input type="number" step="0.01" lang="en-US"
                                        value="<?php
                                        if (is_array($part['cost'])) {
                                            $part['cost'] = $part['cost'][array_key_first($part['cost'])];
                                        }
                                        echo htmlspecialchars(number_format($part['cost'], 2, ".", ""), ENT_QUOTES, 'UTF-8'); ?>"
                                        min="0"
                                        <?= !$part['input'] ? 'disabled' : '' ?>>
                                </td>
                                <td class="elem_total">
                                    <?= htmlspecialchars(number_format($part['cost'], 2, ".", ""), ENT_QUOTES, 'UTF-8') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="4">Итого</th>
                            <th>
                                <span class="table_total" data-result="parts--total">0</span>
                                <span class="table_unit"> руб.</span>
                            </th>
                        </tr>
                    </tfoot>
                </table>

            </div>
        </div>
        <div class="container_block" data-block="goods">
            <div class="block_content">
                <div class="content_sub">
                    <div class="content_item">
                        <span class="item--status">Низкомаржинальный договор!</span>
                        <!-- <p class="item--comment">Lorem ipsum dolor sit amet consectetur adipisicing elit. Quis, beatae earum culpa soluta placeat laboriosam repellat repellendus natus ab maiores quibusdam officiis error quisquam velit sapiente assumenda nemo ipsam iste!</p> -->
                    </div>
                    <div class="content_item">
                        <span class="item--title">Процент:</span>
                        <input type="number" name="" id="good-percent" value="15" min="0">
                        <span class="item--goods">Маржинальность: </span>
                        <span class="item--total">Цена для клиента: </span>
                    </div>
                </div>
                <div class="content_sub">
                    <div class="content_item">
                        <?php
                        switch ($status) {
                            case "send":
                                if ($is_admin) {
                                    ?>
                                    <button class="item--btn btn--submit" data-action="allow">Отправить на доработку</button>
                                    <button class="item--btn btn--submit" data-action="save">Утвердить</button>
                                    <?php
                                } else {
                                    ?>
                                    <button class="item--btn btn--submit" data-action="wait" disabled>На утверждении у
                                        РОПа...</button>
                                    <?php
                                }
                                break;
                            case "allow":
                                if ($is_admin) {
                                    ?>
                                    <button class="item--btn btn--submit" data-action="wait" disabled>Разрешение на изменение
                                        выдано...</button>
                                    <button class="item--btn btn--submit" data-action="save">Утвердить изменения</button>
                                    <?php
                                } else {
                                    ?>
                                    <button class="item--btn btn--submit" data-action="send">Отправить изменения на
                                        утверждение</button>
                                    <?php
                                }
                                break;
                            default:
                            case "save":
                                if ($is_admin) {
                                    ?>
                                    <button class="item--btn btn--submit" data-action="save">Сохранить</button>
                                    <?php
                                } else {
                                    ?>
                                    <button class="item--btn btn--submit" data-action="send">Отправить на утверждение</button>
                                    <?php
                                }
                                break;
                        }
                        ?>
                        <script src="<?= "js/submit.js?" . filemtime(__DIR__ . "/js/submit.js") ?>"></script>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>