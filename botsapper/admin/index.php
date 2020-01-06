<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Панель администратора</title>
    <link rel="stylesheet" type="text/css" href="view.css" media="all">

</head>
<body id="main_body">

<div id="form_container">

    <h1><a>Панель администратора</a></h1>
    <form id="form_62219" class="appnitro" method="post" action="">
        <div class="form_description">
            <h2>Панель администратора</h2>
            <p>Онли фор админс</p>
        </div>
        <ul>
            <?php
            if (isset($_POST['element_666'])) {
                if ($_POST['element_666'] === 'sssr?228?1991?lol?vkcoin?spullae?mulae') {
                    ?>

                    <li id="li_7">
                        <label class="description" for="element_7">Конфиг </label>
                        <div>
                    <textarea id="element_7" name="element_7" cols="80" rows="100"
                              class="element textarea medium"><?= file_get_contents("../config_bot.php"); ?></textarea>
                        </div>
                    </li>
                    <?php
                    if (isset($_POST['element_7'])) {
                        file_put_contents("../config_bot.php", $_POST['element_7']);
                    }
                }
            }
            ?>
            <li id="li_7">
                <label class="description" for="element_666">Пасс </label>
                <div>
                    <input id="element_666" name="element_666" type="password" class="element password medium">
                </div>
            </li>

            <li class="buttons">
                <input type="hidden" name="form_id" value="62219"/>

                <input id="saveForm" class="button_text" type="submit" name="submit" value="Submit"/>
            </li>
        </ul>
    </form>
</div>
</body>
</html>