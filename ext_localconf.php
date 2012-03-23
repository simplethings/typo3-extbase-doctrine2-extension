<?php
if (!defined ('TYPO3_MODE')) die ('Access denied.');

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearCachePostProc'][$_EXTKEY] 
    = t3lib_extMgm::extPath($_EXTKEY) . 'Classes/Utility/Cache.php:Tx_Doctrine2_Utility_Cache->clearProxyCache';
?>