<?php

/**
 * Implements a Doctrine version of the Persistence ManagerInterface
 *
 * @author Benjamin Eberlei <eberlei@simplethings.de>
 */
class Tx_Doctrine2_Manager implements Tx_Extbase_Persistence_ManagerInterface
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * @var Tx_Extbase_Persistence_Mapper_DataMapFactory
     */
    protected $dataMapFactory;

    /**
     * @var Tx_Extbase_Reflection_Service
     */
    protected $reflectionService;

    /**
     * Dev mode flag
     *
     * Lazily loads itself using TYPO3 dev ip mechanism if not set explicitly.
     *
     * @var bool
     */
    static protected $devMode = null;

    /**
     * Flag if autoloading is bootstrapped.
     *
     * @var bool
     */
    static protected $bootstrapped = false;

    static public function setDevMode($devMode)
    {
        self::$devMode = (bool)$devMode;
    }

    static public function getDevMode()
    {
        if (self::$devMode === null) {
            self::$devMode = t3lib_div::cmpIP(
                t3lib_div::getIndpEnv('REMOTE_ADDR'),
                $GLOBALS['TYPO3_CONF_VARS']['SYS']['devIPmask']
            );
        }
        return self::$devMode;
    }


    /**
     * Injects the reflection service
     *
     * @param Tx_Extbase_Reflection_Service $reflectionService
     * @return void
     */
    public function injectReflectionService(Tx_Extbase_Reflection_Service $reflectionService)
    {
        $this->reflectionService = $reflectionService;
    }

    /**
     * @param Tx_Extbase_Persistence_Mapper_DataMapFactory $factory
     */
    public function injectDataMapFactory(Tx_Extbase_Persistence_Mapper_DataMapFactory $factory)
    {
        $this->dataMapFactory = $factory;
    }

    public function getSession()
    {
        throw new \RuntimeException("Deprecated on interface, not implemented.");
    }

    public function getBackend()
    {
        throw new \RuntimeException("Deprecated on interface, not implemented.");
    }

    public function resetEntityManager()
    {
        $this->entityManager = null;
    }

    static private function bootstrapAutoloading()
    {
        if (!self::$bootstrapped) {
            self::$bootstrapped = true;

            require_once __DIR__ . '/../vendor/doctrine-orm/lib/Doctrine/ORM/Tools/Setup.php';
            \Doctrine\ORM\Tools\Setup::registerAutoloadGit(__DIR__ . '/../vendor/doctrine-orm/');

            \Doctrine\Common\Annotations\AnnotationRegistry::registerFile(__DIR__ . "/../vendor/doctrine-orm/lib/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php");
        }
    }

    /**
     * Get the Doctrine EntityManager
     *
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        if ($this->entityManager !== null) {
            return $this->entityManager;
        }

        self::bootstrapAutoloading();

        // Dev Mode decides if proxies are auto-generated every request
        // and what kind of cache is used for the metadata.
        $isDevMode = self::getDevMode();

        $config = new \Doctrine\ORM\Configuration();
        if ($isDevMode) {
            $config->setAutoGenerateProxyClasses(true);
        }
        $config->setProxyDir(PATH_site . 'typo3temp/doctrine2');
        $config->setProxyNamespace('TxDoctrine2Proxies');

        $cache = $this->createCache($isDevMode);
        $config->setMetadataCacheImpl($cache);
        $config->setQueryCacheImpl($cache);

        $paths = $this->getEntityDirectories();
        $driverImpl = $config->newDefaultAnnotationDriver($paths);
        $config->setMetadataDriverImpl($driverImpl);

        $config->addFilter('enableFields', 'Tx_Doctrine2_Persistence_EnableFieldsFilter');

        if ( ! \Doctrine\DBAL\Types\Type::hasType('timestamp')) {
            \Doctrine\DBAL\Types\Type::addType('timestamp', 'Tx_Doctrine2_Types_TimestampType');
        }

        $dbParams = $this->getDatabaseParams();
        $evm = $this->createEventManager();

        $this->entityManager = \Doctrine\ORM\EntityManager::create($dbParams, $config, $evm);
        $this->entityManager->getFilters('enableFields')->enable('enableFields');

        return $this->entityManager;
    }

    protected function getDatabaseParams()
    {
        return array(
            'driver'    => 'pdo_mysql',
            'host'      => TYPO3_db_host,
            'dbname'    => TYPO3_db,
            'user'      => TYPO3_db_username,
            'password'  => TYPO3_db_password,
        );
    }

    protected function createCache($isDevMode)
    {
        if ($isDevMode || ! extension_loaded('apc')) {
            $cache = new \Doctrine\Common\Cache\ArrayCache;
        } else {
            $cache = new \Doctrine\Common\Cache\ApcCache;
        }
        return $cache;
    }

    /**
     * All directories to look for entities.
     *
     * @return array
     */
    protected function getEntityDirectories()
    {
        $paths = array();
        foreach (explode(",", $GLOBALS['TYPO3_CONF_VARS']['EXT']['extList']) as $extKey) {
            if ($extKey == 'extbase') {
                continue;
            }

            $path = t3lib_extMgm::extPath($extKey) . "/Classes/Domain/Model";
            if (file_exists($path)) {
                $paths[] = $path;
            }
        }
        return $paths;
    }

    protected function createEventManager()
    {
        $metadataService = new Tx_Doctrine2_Mapping_TYPO3MetadataService();
        $metadataService->injectReflectionService($this->reflectionService);
        $metadataService->injectDataMapFactory($this->dataMapFactory);

        $metadataListener = new Tx_Doctrine2_Mapping_TYPO3TCAMetadataListener;
        $metadataListener->injectMetadataService($metadataService);

        $evm = new \Doctrine\Common\EventManager;
        $evm->addEventSubscriber($metadataListener);

        $this->configureEventManager($evm);

        return $evm;
    }

    /**
     * Hook method to register own event managers
     *
     * @return void
     */
    protected function configureEventManager(\Doctrine\Common\EventManager $evm)
    {

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
        throw new \RuntimeException("not implemented, use Repository");
    }

    public function getObjectCountByQuery(Tx_Extbase_Persistence_QueryInterface $query)
    {
        throw new \RuntimeException("not implemented, use Repository");
    }

    public function registerRepositoryClassName($className)
    {
        // not needed
    }
}
