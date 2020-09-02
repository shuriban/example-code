<?php

namespace Firstbit\GitLog;

defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

use Bitrix\Main\Loader;

IncludeModuleLangFile(__FILE__);

Loader::registerAutoLoadClasses(
    'firstbit.gitlog',
	array(
		'\Firstbit\GitLog\HooksHandler' => 'lib/HooksHandler.php',
		'\Firstbit\GitLog\HLBlock' => 'lib/HLBlock.php',
		'\Firstbit\GitLog\Main' => 'lib/Main.php'
	)
);