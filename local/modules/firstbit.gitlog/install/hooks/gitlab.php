<?php
define("NOT_CHECK_PERMISSIONS", true);
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");


use \Bitrix\Main\Loader;
use \FirstBit\GitLog\Main;
Loader::includeModule('firstbit.gitlog');


$obGitLog = new Main;
$obGitLog->getHook();

