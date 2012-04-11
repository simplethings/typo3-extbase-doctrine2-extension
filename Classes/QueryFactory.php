<?php

class Tx_Doctrine2_QueryFactory implements Tx_Extbase_Persistence_QueryFactoryInterface
{
    /**
     * @var Tx_Doctrine2_Manager
     */
    protected $manager;

    /**
     * @param Tx_Doctrine2_Manager
     * @return void
     */
    public function injectManager(Tx_Doctrine2_Manager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @return Tx_Doctrine2_Query
     */
    public function create($className)
    {
        $query = new Tx_Doctrine2_Query($className);
        $query->injectEntityManager($this->manager->getEntityManager());
        return $query;
    }
}

