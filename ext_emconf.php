<?php

########################################################################
# Extension Manager/Repository config file for ext "doctrine2".
#
# Auto generated 23-03-2012 15:00
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Doctrine2',
	'description' => 'This TYPO3 4.6+ extension completly replaces the Extbase ORM with Doctrine2.',
	'category' => 'misc',
	'shy' => '',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'alpha',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => 'typo3temp/doctrine2',
	'modify_tables' => '',
	'clearCacheOnLoad' => 1,
	'lockType' => '',
	'author' => 'Benjamin Eberlei, Hendrik Nadler',
	'author_email' => 'eberlei@simplethings.de',
	'author_company' => 'www.simplethings.de',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'version' => '0.0.1',
	'_md5_values_when_last_written' => '',
	'constraints' => array(
		'depends' => array(
			'php' => '5.3.0-0.0.0',
			'typo3' => '4.6.0-0.0.0',
                        'extbase' => '1.4.0-0.0.0'
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'suggests' => array(
	),
);

?>