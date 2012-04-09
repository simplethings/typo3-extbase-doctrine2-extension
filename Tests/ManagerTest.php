<?php

class Tx_Doctrine2_Tests_ManagerTest extends Tx_Doctrine2_Tests_TestCase
{
    public function testGetEntityManager()
    {
        $rs = new Tx_Extbase_Reflection_Service();
        $factory = $this->getMock('Tx_Extbase_Persistence_Mapper_DataMapFactory', array(), array(), '', false);
        $manager = new Tx_Doctrine2_Manager();
        $manager->injectReflectionService($rs);
        $manager->injectDataMapFactory($factory);

        $em = $manager->getEntityManager();

        $this->assertInstanceOf('Doctrine\ORM\EntityManager', $em);
    }
}

