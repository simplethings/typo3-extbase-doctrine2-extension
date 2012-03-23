<?php
$extensionClassesPath = t3lib_extMgm::extPath('doctrine2') . 'Classes/';

return array(
 	'tx_doctrine2_extbasebootstrap' => $extensionClassesPath . 'ExtbaseBootstrap.php',
 	'tx_doctrine2_utility_extension' => $extensionClassesPath . 'Utility/Extension.php',
 	'tx_doctrine2_utility_cache' => $extensionClassesPath . 'Utility/Cache.php',
);

?>