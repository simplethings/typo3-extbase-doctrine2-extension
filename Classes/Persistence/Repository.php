<?php

class Tx_Doctrine2_Persistence_Repository implements Tx_Extbase_Persistence_RepositoryInterface
{
    protected $doctrine2Manager;
    
    protected $entityManager;
    
    protected $objectManager;
    
    protected $addedObjects = array();
    protected $removedObjects = array();
    
    protected $objectType;
    
    protected $defaultOrderings = array();
    protected $defaultQuerySettings = array();
    
    /**
     * @param Tx_Extbase_Object_ObjectManagerInterface $objectManager 
     */
    public function __construct(Tx_Extbase_Object_ObjectManagerInterface $objectManager = NULL)
    {
        $this->objectType = preg_replace(array('/_Repository_(?!.*_Repository_)/', '/Repository$/'), array('_Model_', ''), $this->getRepositoryClassName());
        $this->objectManager = $objectManager;
    }
    
    protected function getRepositoryClassName()
    {
        return get_class($this);
    }
    
    public function injectDoctrine2Manager(Tx_Doctrine2_Manager $doctrine2Manager)
    {
        $this->doctrine2Manager = $doctrine2Manager;
        $this->entityManager = $doctrine2Manager->getEntityManager();
    }
    
    public function add($object)
    {
        $this->entityManager->persist($object);
        $this->addedObjects[spl_object_hash($object)] = $objects;
    }
    
    public function remove($object)
    {
        $this->entityManager->remove($object);
        $this->removedObjects[spl_object_hash($object)] = $object;
    }
    
    public function replace($existingObject, $newObject)
    {
        $this->entityManager->merge($newObject);
    }
    
    public function update($modifiedObject)
    {
        $this->entityManager->merge($modifiedObject);
    }
    
    public function getAddedObjects()
    {
        return array_values($this->addedObjects);
    }
    
    public function getRemovedObjects()
    {
        return array_values($this->removedObjects);
    }
    
    public function findAll() 
    {
        return $this->entityManager->getRepository($this->objectType)->findBy(array(), $this->defaultOrderings);
    }
    
    public function countAll() 
    {
        return $this->createQuery()->count();
    }
    
    public function removeAll()
    {
        foreach ($this->findAll() as $object) {
            $this->remove($object);
        }
    }
    
    public function findByUid($uid)
    {
        return $this->entityManager->getRepository($this->objectType)->find($uid);
    }
    
    public function setDefaultOrderings(array $defaultOrderings) 
    {
        $this->defaultOrderings = $defaultOrderings;
    }
    
    public function setDefaultQuerySettings(Tx_Extbase_Persistence_QuerySettingsInterface $defaultQuerySettings) 
    {
        $this->defaultQuerySetings = $defaultQuerySettings;
    }
    
    public function createQuery()
    {
        $query = new Tx_Doctrine2_Query($this->objectType);
        $query->injectEntityManager($this->entityManager);
        
        if ($this->defaultOrderings) {
            $query->setOrderings($this->defaultOrderings);
        }
        
        if ($this->defaultQuerySettings) {
            $query->setQuerySettings($this->defaultQuerySettings);
        }
        
        return $query;
    }
    
    public function createDqlQuery($dql)
    {
        return $this->entityManager->createQuery($dql);
    }
    
    public function createQueryBuilder()
    {
         return $this->entityManager->getRepository($this->objectType)->createQueryBuilder();
    }
}