<?php

use Doctrine\Common\EventSubscriber;

/**
 * For new entities set the storage pid as configured or set in the Entity.
 *
 * @author Benjamin Eberlei <eberlei@simplethings.de>
 */
class Tx_Doctrine2_Persistence_StoragePidListener implements EventSubscriber
{
    private $configurationManager;

	/**
	 * @param Tx_Extbase_Configuration_ConfigurationManagerInterface $configurationManager
	 * @return void
	 */
	public function injectConfigurationManager(Tx_Extbase_Configuration_ConfigurationManagerInterface $configurationManager)
    {
		$this->configurationManager = $configurationManager;
	}

    public function getSubscribedEvents()
    {
        return array('prePersist');
    }

    public function prePersist($eventArgs)
    {
        $entity = $eventArgs->getEntity();
        $class = $eventArgs->getEntityManager()->getClassMetadata(get_class($entity));

        if ( ! isset($class->fieldMappings['pid'])) {
            return;
        }

        $pid = $class->reflFields['pid']->getValue($entity);
        if ($pid !== null) {
            return;
        }

        $entity->setPid($this->determineStoragePidForNewRecord($entity));
    }

    protected function determineStoragePidForNewRecord($entity)
    {
		$frameworkConfiguration = $this->configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        $className = \Doctrine\Common\Util\ClassUtils::getClass($object);

        if (isset($frameworkConfiguration['persistence']['classes'][$className]) && !empty($frameworkConfiguration['persistence']['classes'][$className]['newRecordStoragePid'])) {
            return (int)$frameworkConfiguration['persistence']['classes'][$className]['newRecordStoragePid'];
        }

		$storagePidList = t3lib_div::intExplode(',', $frameworkConfiguration['persistence']['storagePid']);
		return (int) $storagePidList[0];
    }
}

