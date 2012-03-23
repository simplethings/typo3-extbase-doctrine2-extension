<?php

class Tx_Doctrine2_Manager implements Tx_Extbase_Persistence_ManagerInterface
{
    private $entityManager;

    public function getSession()
    {
        throw new \RuntimeException("Deprecated on interface, not implemented.");
    }

    public function getBackend()
    {
        throw new \RuntimeException("Deprecated on interface, not implemented.");
    }

    public function getEntityManager()
    {
        if ($this->entityManager === null) {
            $db = $GLOBALS['TYPO3_DB'];
            // Bootstrap doctrine

            $isDevMode = t3lib_div::cmpIP(t3lib_div::getIndpEnv('REMOTE_ADDR'), $GLOBALS['TYPO3_CONF_VARS']['SYS']['devIPmask']);
            if ($isDevMode || ! extension_loaded('apc')) {
                $cache = new \Doctrine\Common\Cache\ArrayCache;
            } else {
                $cache = new \Doctrine\Common\Cache\ApcCache;
            }

            require_once __DIR__ . '/../vendor/doctrine-orm/lib/Doctrine/ORM/Tools/Setup.php';
            \Doctrine\ORM\Tools\Setup::registerAutoloadGit(__DIR__ . '/../vendor/doctrine-orm/lib';

            $config = new \Doctrine\ORM\Configuration();
            if ($isDevMode) {
                $config->setAutoGenerateProxyClasses(true);
            }
            $config->setMetadataCacheImpl($cache);
            $config->setQueryCacheImpl($cache);
            $config->setProxyDir(PATH_site . 'typo3temp/doctrine2');
            $config->setProxyNamespace('TxDoctrine2Proxies');

            $paths = array();
            foreach (explode(",", $GLOBALS['TYPO3_CONF_VARS']['EXT']['extList']) as $extKey) {
                $path = t3lib_extMgm::extPath($extKey);
                $paths[] = $path . "/Classes/Domain/Entity";
            }

            $driverImpl = $config->newDefaultAnnotationDriver($paths);
            $config->setMetadataDriverImpl($driverImpl);

            $dbParams = array(
                'driver' => 'pdo_mysql',
                'host' => TYPO3_db_host,
                'dbname' => TYPO3_db,
                'user' => TYPO3_db_username,
                'password' => TYPO3_db_password,
            );

            $evm = new \Doctrine\Common\EventManager;
            $evm->addEventSubscriber();

            $this->entityManager = \Doctrine\ORM\EntityManager::create($dbParams, $config, $evm);
        }
        return $this->entityManager;
    }

    public function persistAll()
    {
        $this->getEntityManager()->flush();
    }

    public function getIdentifierByObject($object)
    {
        if ( ! $this->getEntityManager()->contains($object)) {
            return null;
        }

        $id = $this->getEntityManager()->getUnitOfWork($object)->getEntityIdentifier($object);
        if (count($id) == 1) {
            return current($id);
        }
        return $id;
    }

    public function getObjectByIdentifier($identifier, $objectType)
    {
        return $this->getEntityManager()->find($objectType, $identifier);
    }

    public function getObjectDataByQuery(Tx_Extbase_Persistence_QueryInterface $query)
    {

    }

	public function getObjectDataByQuery(Tx_Extbase_Persistence_QueryInterface $query);
    {

    }

	public function registerRepositoryClassName($className)
    {
        // not needed
    }
}
