<?php

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Configuration;
use Doctrine\Common\EventManager;

class Tx_Doctrine2_Tests_TestCase extends PHPUnit_Framework_TestCase
{
    protected $entityManager;

    public function setUp()
    {
        $params = array('driver' => 'pdo_sqlite', 'memory' => true);
        $config = new Configuration();
        $config->setMetadataDriverImpl($config->newDefaultAnnotationDriver());
        $config->setProxyDir(sys_get_temp_dir());
        $config->setProxyNamespace('MyProxy');
        $listener = new Tx_Doctrine2_Mapping_TYPO3TCAMetadataListener();
        $listener->injectMetadataService(new Tx_Doctrine2_Tests_Model_MockMetadataService());

        $evm = new EventManager();
        $evm->addEventSubscriber($listener);
        $this->entityManager = EntityManager::create($params, $config, $evm);
    }

    public function setupDatabase(array $classes)
    {
        $metadata = array();
        foreach ($classes as $className)  {
            $metadata[] = $this->entityManager->getClassMetadata($className);
        }

        $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($this->entityManager);
        $schemaTool->createSchema($metadata);
    }
}

