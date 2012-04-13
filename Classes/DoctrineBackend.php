<?php

/**
 * Dummy implementation just to have getIdentifierByObject()
 */
class Tx_Doctrine2_DoctrineBackend implements Tx_Extbase_Persistence_BackendInterface
{
	public function setAggregateRootObjects(Tx_Extbase_Persistence_ObjectStorage $objects) {}
	public function setDeletedObjects(Tx_Extbase_Persistence_ObjectStorage $objects) {}
	public function commit() {}
	public function getIdentifierByObject($object)
    {
        return $object->getUid();
    }

	public function getObjectByIdentifier($identifier, $className) {}
	public function isNewObject($object) {}
	public function replaceObject($existingObject, $newObject) {}
}
