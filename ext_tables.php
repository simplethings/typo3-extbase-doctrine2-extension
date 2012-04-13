<?php
if (!defined ('TYPO3_MODE')) die ('Access denied.');

if (isset($TBE_MODULES['_dispatcher'])) {
    $key = array_search('Tx_Extbase_Core_Bootstrap', $TBE_MODULES['_dispatcher']);
    $TBE_MODULES['_dispatcher'][$key] = 'Tx_Doctrine2_ExtbaseBootstrap';
} else {
    $TBE_MODULES['_dispatcher'][] = 'Tx_Doctrine2_ExtbaseBootstrap';
}

