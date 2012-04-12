<?php

use Doctrine\Common\Persistence\ObjectManager;

/**
 * Repository for Entities managed by Doctrine2 (instead of Extbase ORM)
 *
 * @author Benjamin Eberlei <eberlei@simplethings.de>
 * @author Hendrik Nadler <nadler@simplethings.de>
 */
class Tx_Doctrine2_Persistence_Repository implements Tx_Extbase_Persistence_RepositoryInterface
{
    /**
     * @var Doctrine\Common\Persistence\ObjectManager
     */
    protected $entityManager;

    /**
     * TYPO3 Object Manager is passed to constructor of repository.
     * This is not really needed in the "base" code but may be used by
     * implementors.
     *
     * @var object
     */
    protected $objectManager;

    /**
     * @var Tx_Doctrine2_QueryFactory
     */
    protected $queryFactory;

    /**
     * All the objects added to the repository during the scope
     * of the current "object transaction".
     *
     * @var array
     */
    protected $addedObjects = array();

    /**
     * All the objects removed from the repository during the scope of the
     * current "object transaction".
     *
     * @var array
     */
    protected $removedObjects = array();

    /**
     * Name of the class this repository is reponsible for.
     *
     * @var string
     */
    protected $objectType;

    /**
     * Default orderings of this repository.
     *
     * @var array
     */
    protected $defaultOrderings = array();

    /**
     * Query Settings
     *
     * @var Tx_Extbase_Persistence_QuerySettingsInterface
     */
    protected $defaultQuerySettings;

    /**
     * @param Tx_Extbase_Object_ObjectManagerInterface $objectManager
     */
    public function __construct(Tx_Extbase_Object_ObjectManagerInterface $objectManager = null)
    {
        $this->objectType = preg_replace(array('/_Repository_(?!.*_Repository_)/', '/Repository$/'), array('_Model_', ''), $this->getRepositoryClassName());
        $this->objectManager = $objectManager;
    }

    public function getObjectType()
    {
        return $this->objectType;
    }

    public function injectDoctrine2Manager(Tx_Doctrine2_Manager $doctrine2Manager)
    {
        $this->entityManager = $doctrine2Manager->getEntityManager();
    }

    public function setEntityManager(ObjectManager $em)
    {
        $this->entityManager = $em;
    }

    public function injectQueryFactory(Tx_Doctrine2_QueryFactory $queryFactory)
    {
        $this->queryFactory = $queryFactory;
    }

    protected function getRepositoryClassName()
    {
        return get_class($this);
    }

    public function add($object)
    {
        $this->assertIsMatchingObjectType($object);

        $this->entityManager->persist($object);
        $this->addedObjects[spl_object_hash($object)] = $object;
    }

    private function assertIsMatchingObjectType($object)
    {
        if ( ! ($object instanceof $this->objectType)) {
            throw new \InvalidArgumentException("Expected object of type " . $this->objectType . " but got object of type " . get_class($object));
        }
    }

    public function remove($object)
    {
        $this->assertIsMatchingObjectType($object);

        $this->entityManager->remove($object);
        $this->removedObjects[spl_object_hash($object)] = $object;
    }

    public function replace($existingObject, $newObject)
    {
        $this->assertIsMatchingObjectType($existingObject);
        $this->assertIsMatchingObjectType($newObject);

        $this->entityManager->merge($newObject);
    }

    public function update($modifiedObject)
    {
        $this->assertIsMatchingObjectType($modifiedObject);

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
        return $this->createQuery()->execute();
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
        $this->defaultQuerySettings = $defaultQuerySettings;
    }

    public function createQuery()
    {
        $query = $this->queryFactory->create($this->objectType);

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

    public function createQueryBuilder($rootAlias = 'r')
    {
         return $this->entityManager->getRepository($this->objectType)->createQueryBuilder($rootAlias);
    }
}
