<?php
declare(strict_types=1);

require_once __DIR__ . "/../PHP_Scripts/load_file.php";


/*
--------------------------------------------
Clear basket
--------------------------------------------
*/

$_SESSION["basket"] = [];//emplty the basket array in the session to clear it

/*
--------------------------------------------
Redirect to gift shop
--------------------------------------------
*/

header("Location: /PHP_Form/gift_shop_form.php");
exit();