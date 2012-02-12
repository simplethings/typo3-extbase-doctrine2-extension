<?php

class Tx_Doctrine2_Tests_DoctrineBackendTest extends Tx_Doctrine2_Tests_TestCase
{
    /**
     * @var Tx_Doctrine2_DoctrineBackend
     */
    private $backend;

    public function setUp()
    {
        parent::setUp();

        $this->backend = new Tx_Doctrine2_DoctrineBackend();
        $this->backend->injectEntityManager($this->entityManager);
    }

    public function testSetAggregateRootObjects()
    {
        $post = new Tx_Doctrine2_Tests_Model_Post();
        $objects = new Tx_Extbase_Persistence_ObjectStorage();
        $objects->attach($post);
        $this->backend->setAggregateRootObjects($objects);
    }
}

