<?
header("Access-Control-Allow-Origin: *");
foreach (glob($_SERVER["DOCUMENT_ROOT"] . "/local/application/hr/service/*.php") as $filename) {
    require_once($filename);
}
$config = json_decode(file_get_contents('json/config.json'), true);
$fields = json_decode(file_get_contents('json/fields.json'), true);
$departments = getDepartments();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.css" />

    <?
    foreach (glob('css/*.css') as $css) {
        ?>
        <link rel="stylesheet" href="<?= $css ?>?<?= filemtime($css) ?>">
        <?
    };
    ?>

    <script src="//api.bitrix24.com/api/v1/"></script>
    <script src="//api.bitrix24.com/api/v1/pull/"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js" integrity="sha512-pHVGpX7F/27yZ0ISY+VVjyULApbDlD0/X0rgGbTqCE7WFW5MezNTWG/dnhtbBuICzsd0WQPgpE4REBLv+UqChw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="js/inputFields.js?<?= filemtime('js/inputFields.js') ?>"></script>
    <script src="js/script.js?<?= filemtime('js/script.js') ?>"></script>
</head>

<body>
    <input type="hidden" id="departments" value="<?= htmlspecialchars(json_encode($departments)) ?>">
    <input type="hidden" id="config" value="<?= htmlspecialchars(json_encode($config)) ?>">
    <input type="hidden" id="fields" value="<?= htmlspecialchars(json_encode($fields)) ?>">
    <input type="hidden" id="connection" value="<?= htmlspecialchars(json_encode($_REQUEST)) ?>">
    <div id="login-modal" class="modal"></div>
    <div class="wrap">
        <header>
            <div class="head_logo">
                <img src="<?= "img/crm/" . htmlspecialchars($domain, ENT_QUOTES, 'UTF-8') . "/header.png" ?>" alt="" class="head_logo--img" />
                <h1 class="head_logo--title">
                    <?= $group_info['NAME'] ?>
                </h1>
            </div>
        </header>
        <section>
            <?
            foreach ($config as $key => $value) {
                ?>
                <div class="content_item" id="<?= $key ?>">
                    <img src="img/icons/<?= $key ?>.png" alt="" class="item--icon">
                    <span class="item--title"><?= $value['title'] ?></span>
                    <p class="item--desc"><?= $value['desc'] ?></p>
                </div>
                <?
            }
            ?>
        </section>
    </div>
</body>

</html>