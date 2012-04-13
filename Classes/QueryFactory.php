<?php

class Tx_Doctrine2_QueryFactory implements Tx_Extbase_Persistence_QueryFactoryInterface
{
    /**
     * @var Tx_Extbase_Persistence_ManagerInterface
     */
    protected $manager;

	/**
	 * @var Tx_Extbase_Object_ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @var Tx_Extbase_Configuration_ConfigurationManagerInterface
	 */
	protected $configurationManager;

    /**
     * @param Tx_Extbase_Persistence_ManagerInterface
     * @return void
     */
    public function injectManager(Tx_Extbase_Persistence_ManagerInterface $manager)
    {
        $this->manager = $manager;
    }

	public function injectObjectManager(Tx_Extbase_Object_ObjectManagerInterface $objectManager)
    {
		$this->objectManager = $objectManager;
	}

	/**
	 * @param Tx_Extbase_Configuration_ConfigurationManagerInterface $configurationManager
	 * @return void
	 */
	public function injectConfigurationManager(Tx_Extbase_Configuration_ConfigurationManagerInterface $configurationManager)
    {
		$this->configurationManager = $configurationManager;
	}

    /**
     * @return Tx_Doctrine2_Query
     */
    public function create($className)
    {
        $query = new Tx_Doctrine2_Query($className);
        $query->injectEntityManager($this->manager->getEntityManager());

        if (!$this->objectManager || !$this->configurationManager) {
            return $query;
        }

		$querySettings = $this->objectManager->create('Tx_Extbase_Persistence_QuerySettingsInterface');
		$frameworkConfiguration = $this->configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
		$querySettings->setStoragePageIds(t3lib_div::intExplode(',', $frameworkConfiguration['persistence']['storagePid']));
		$query->setQuerySettings($querySettings);

        return $query;
    }
}

