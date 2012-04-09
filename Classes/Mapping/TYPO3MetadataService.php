<?php

/**
 * Act as an anti-corruption layer between TYPO3 Metadata Mess and the TYPO3
 * Extbase-Mapping Driver.
 *
 * @author Benjamin Eberlei <eberlei@simplethings.de>
 */
class Tx_Doctrine2_Mapping_TYPO3MetadataService implements Tx_Doctrine2_Mapping_MetadataService
{
    /**
     * @var Tx_Extbase_Persistence_Mapper_DataMapFactory
     */
    protected $dataMapFactory;

    /**
     * @var Tx_Extbase_Reflection_Service
     */
    protected $reflectionService;

    /**
     * Injects the reflection service
     *
     * @param Tx_Extbase_Reflection_Service $reflectionService
     * @return void
     */
    public function injectReflectionService(Tx_Extbase_Reflection_Service $reflectionService)
    {
        $this->reflectionService = $reflectionService;
    }

    /**
     * @param Tx_Extbase_Persistence_Mapper_DataMapFactory $factory
     */
    public function injectDataMapFactory(Tx_Extbase_Persistence_Mapper_DataMapFactory $factory)
    {
        $this->dataMapFactory = $factory;
    }

    /**
     * @return Tx_Extbase_Persistence_Mapper_DataMap
     */
    public function getDataMap($className)
    {
        return $this->dataMapFactory->buildDataMap($className);
    }

    /**
     * @throws RuntimeException
     * @param string $className
     * @param string $propertyName
     * @return string
     */
    public function getTargetEntity($className, $propertyName)
    {
        $propertyMetaData = $this->reflectionService->getClassSchema($className)->getProperty($propertyName);
        if (!empty($propertyMetaData['elementType'])) {
            $type = $propertyMetaData['elementType'];
        } elseif (!empty($propertyMetaData['type'])) {
            $type = $propertyMetaData['type'];
        } else {
            throw new \RuntimeException("Cannot guess target entity from $className#$propertyName using Extbase metadata.");
        }
        return $type;
    }

    public function getTCAColumnType($tableName, $columnName)
    {
        global $TCA;
        if (!isset($TCA[$tableName]['columns'][$columnName]['config']['type'])) {
            throw new \RuntimeException("Cannot find column $tableName.$columnName in TCA");
        }
        switch ($TCA[$tableName]['columns'][$columnName]['config']['type']) {
            case 'input':
                $data = $TCA[$tableName]['columns'][$columnName]['config'];
                if (isset($data['eval'])) {
                    if (strpos($data['eval'], "datetime") !== false) {
                        return "timestamp";
                    }
                }
                return 'string';
            case 'text':
                return 'text';
            case 'check':
                return 'boolean';
            case 'radio':
            case 'select':
                return 'string';
            default:
                return 'string';
        }
    }
}
