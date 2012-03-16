<?php

use Doctrine\ORM\Mapping\Driver\Driver;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\Common\EventSubscriber;

/**
 * This mapping driver uses Class Docblocks and TCA Mapping Data to build the
 * ClassMetadata mapping scheme of a loaded entity class.
 *
 * @author Benjamin Eberlei <eberlei@simplethings.de>
 */
class Tx_Doctrine2_Mapping_TYPO3TCAMetadataListener implements EventSubscriber
{
    /**
     * @var Tx_Doctrine2_Mapping_MetadataService
     */
    protected $metadataService;

    /**
     * @param Tx_Doctrine2_Mapping_MetadataService $service
     * @return void
     */
    public function injectMetadataService(Tx_Doctrine2_Mapping_MetadataService $service)
    {
        $this->metadataService = $service;
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $event)
    {
        if (!($this->metadataService instanceof Tx_Doctrine2_Mapping_MetadataService)) {
            throw new \RuntimeException("Cannot load Typo3 Metadata without Tx_Doctrine2_Mapping_MetadataService being set on metadata listener.");
        }

        $metadata = $event->getClassMetadata();
        $className = $metadata->name;

        if ($className == 'Tx_Doctrine2_DomainObject_AbstractDomainObject') {
            $metadata->isMappedSuperclass = true;

            $metadata->mapField(array(
                'fieldName' => 'uid',
                'columnName' => 'uid',
                'id' => true,
                'type' => 'integer',
            ));
            $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_AUTO);
            return;
        }

        $dataMap = $this->metadataService->getDataMap($className);
        if (!$dataMap) {
            return;
        }

        $metadata->setPrimaryTable(array('name' => $dataMap->getTableName()));
        // TODO: Save EnableFields and Other metadata stuff into primary table
        // array for later reference in filters and listeners.

        if ($pidColumnName = $dataMap->getPageIdColumnName()) {
            $metadata->mapField(array(
                'fieldName' => 'pid',
                'columnName' => $pidColumnName,
                'type' => 'integer',
                'inherited' => 'Tx_Extbase_DomainObject_AbstractDomainObject',
            ));
        }

        if ($lidColumnName = $dataMap->getLanguageIdColumnName()) {
            $metadata->mapField(array(
                'fieldName' => 'languageUid',
                'columnName' => $lidColumnName,
                'type' => 'integer',
                'inherited' => 'Tx_Extbase_DomainObject_AbstractDomainObject',
            ));
        }

        $reflClass = new \ReflectionClass($metadata->name);

        // only map to properties that actually exist on the class.
        foreach ($reflClass->getProperties() as $property) {
            if ($property->isStatic() || ! $dataMap->isPersistableProperty($property->getName())) {
                continue;
            }

            $columnMap = $dataMap->getColumnMap($property->getName());

            switch ($columnMap->getTypeOfRelation()) {
                case Tx_Extbase_Persistence_Mapper_ColumnMap::RELATION_NONE:
                    $metadata->mapField(array(
                        'fieldName' => $columnMap->getPropertyName(),
                        'columnName' => $columnMap->getColumnName(),
                        'type' => $this->metadataService->getTCAColumnType($dataMap->getTableName(), $columnMap->getColumnName()),
                    ));
                    break;
                case Tx_Extbase_Persistence_Mapper_ColumnMap::RELATION_HAS_ONE:
                    $metadata->mapManyToOne(array(
                        'fieldName' => $columnMap->getPropertyName(),
                        'targetEntity' => $this->metadataService->getTargetEntity($metadata->name, $columnMap->getPropertyName()),
                        'joinColumns' => array(
                            array('name' => $columnMap->getColumnName(), 'referencedColumnName' => $columnNamp->getParentKeyTableFieldName(),
                        ),
                    )));
                default:
                    throw new \RuntimeException(sprintf(
                        "Relation type %s is not yet supported in %s#%s",
                        $columnMap->getTypeOfRelation(), $metadata->name, $columnMap->getPropertyName()
                    ));
            }
        }
    }

    public function getAllClassNames()
    {
        // TODO: Delegate to metadata service, getting this from TYPO3
        return array();
    }

    public function isTransient($className)
    {
        // TODO: Can we relax this more to the interface?
        return !($className instanceof Tx_Extbase_DomainObject_AbstractDomainObject);
    }

    public function getSubscribedEvents()
    {
        return array('loadClassMetadata');
    }
}

