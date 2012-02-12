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
        $evm = new EventManager();
        $this->entityManager = EntityManager::create($params, $config, $evm);
    }
}

