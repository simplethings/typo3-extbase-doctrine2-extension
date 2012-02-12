<?php

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\EntityManager;

class Tx_Doctrine2_DoctrineBackend implements Tx_Extbase_Persistence_BackendInterface
{
    /**
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;

    public function injectEntityManager(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Sets the aggregate root objects
     *
     * @param Tx_Extbase_Persistence_ObjectStorage $objects
     * @return void
     */
    public function setAggregateRootObjects(Tx_Extbase_Persistence_ObjectStorage $objects)
    {
        foreach ($objects as $object) {
            $class = $this->entityManager->getClassMetadata(get_class($object));
            if ( ! $this->entityManager->contains($object) || ! $class->isChangeTrackingDeferredImplicit()) {
                $this->entityManager->persist($object);
            }
        }
    }

    /**
     * Sets the deleted objects
     *
     * @param Tx_Extbase_Persistence_ObjectStorage $objects
     * @return void
     */
    public function setDeletedObjects(Tx_Extbase_Persistence_ObjectStorage $objects)
    {
        foreach ($objects as $object) {
            $this->entityManager->remove($object);
        }
    }

    /**
     * Commits the current persistence session
     *
     * @return void
     */
    public function commit()
    {
        $this->entityManager->flush();
    }

    /**
     * Returns the (internal) identifier for the object, if it is known to the
     * backend. Otherwise NULL is returned.
     *
     * @param object $object
     * @return string The identifier for the object if it is known, or NULL
     */
    public function getIdentifierByObject($object)
    {
        if ($object instanceof Tx_Extbase_DomainObject_DomainObjectInterface) {
            return $object->getUid();
        }
        return null;
    }

    /**
     * Returns the object with the (internal) identifier, if it is known to the
     * backend. Otherwise NULL is returned.
     *
     * @param string $identifier
     * @param string $className
     * @return object The object for the identifier if it is known, or NULL
     */
    public function getObjectByIdentifier($identifier, $className)
    {
        return $this->entityManager->find($className, $identifier);
    }

    /**
     * Checks if the given object has ever been persisted.
     *
     * @param object $object The object to check
     * @return boolean TRUE if the object is new, FALSE if the object exists in the repository
     */
    public function isNewObject($object)
    {
        return ($this->entityManager->getUnitOfWork()->getEntityState($object, \Doctrine\ORM\UnitOfWork::STATE_NEW) === \Doctrine\ORM\UnitOfWork::STATE_NEW);
    }

    /**
     * Replaces the given object by the second object.
     *
     * This method will unregister the existing object at the identity map and
     * register the new object instead. The existing object must therefore
     * already be registered at the identity map which is the case for all
     * reconstituted objects.
     *
     * The new object will be identified by the uuid which formerly belonged
     * to the existing object. The existing object looses its uuid.
     *
     * @param object $existingObject The existing object
     * @param object $newObject The new object
     * @return void
     */
    public function replaceObject($existingObject, $newObject)
    {
        $this->entityManager->detach($existingObject);
        $newObject->setUid($existingObject->getUid());
        $newObject = $this->entityManager->merge($newObject);
    }
}

