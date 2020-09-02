<?php
CModule::AddAutoloadClasses(null, [
        '\FirstBit\Prices' => '/local/php_interface/firstbit/classes/prices.php',
        '\FirstBit\UserProfiles' => '/local/php_interface/firstbit/classes/userProfiles.php'
]);
require('store_config.php');