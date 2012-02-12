<?php

$vendorOrm = __DIR__ . '/../vendor/doctrine-orm';
require_once $vendorOrm . '/lib/vendor/doctrine-common/lib/Doctrine/Common/ClassLoader.php';
require_once $vendorOrm . '/lib/Doctrine/ORM/Tools/Setup.php';

\Doctrine\ORM\Tools\Setup::registerAutoloadGit($vendorOrm);

/**
 * Typo3, Extbase and Extension Autoloading
 */
spl_autoload_register(function($class) {
    if (strpos($class, 'Tx_Extbase') === 0) {
        $class = str_replace('Tx_Extbase_', '', $class);
        $dir = __DIR__ . '/../vendor/extbase/Classes';
    } else if (strpos($class, 'Tx_Doctrine2_Tests') === 0) {
        $class = str_replace('Tx_Doctrine2_Tests_', '', $class);
        $dir = __DIR__;
    } else if (strpos($class, 'Tx_Doctrine2') === 0) {
        $class = str_replace('Tx_Doctrine2_', '', $class);
        $dir = __DIR__ . '/../Classes';
    }

    if (isset($class) && isset($dir)) {
        require $dir . '/' . str_replace("_", "/", $class) . '.php';
    }
});

