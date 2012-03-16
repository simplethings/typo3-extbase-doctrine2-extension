<?php
/**
 * Interface for Metadata grabbed from TYPO3 instead of Doctrine
 *
 * @author Benjamin Eberlei <eberlei@simplethings.de>
 */
interface Tx_Doctrine2_Mapping_MetadataService
{
    /**
     * @return Tx_Extbase_Persistence_Mapper_DataMap
     */
    public function getDataMap($className);

    /**
     * @throws RuntimeException
     * @param string $className
     * @param string $propertyName
     * @return string
     */
    public function getTargetEntity($className, $propertyName);

    /**
     * Get a Doctrine2 Type from a TCA Column Type
     *
     * @param string $tableName
     * @param string $columnName
     * @return string
     */
    public function getTCAColumnType($tableName, $columnName);
}
