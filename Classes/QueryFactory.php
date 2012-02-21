<?php

class Tx_Doctrine2_QueryFactory implements Tx_Extbase_Persistence_QueryFactoryInterface
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $entityManager
     * @return void
     */
    public function injectEntityManager(\Doctrine\Common\Persistence\ObjectManager $entityManager
    {
        $this->entityManager = $entityManager;
    }

    public function create($className)
    {
        $query = new Tx_Doctrine2_Query($className);
        $query->injectEntityManager($this->entityManager);
        return $query;
    }
}

