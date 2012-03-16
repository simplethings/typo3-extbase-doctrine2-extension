<?php
class Tx_Doctrine2_Tests_Model_MockMetadataService implements Tx_Doctrine2_Mapping_MetadataService
{
    /**
     * @return Tx_Extbase_Persistence_Mapper_DataMap
     */
    public function getDataMap($className)
    {
        switch($className) {
            case 'Tx_Doctrine2_Tests_Model_Post':
                $dataMap = new Tx_Extbase_Persistence_Mapper_DataMap('Tx_Doctrine2_Tests_Model_Post', 'posts');
                $dataMap->setPageIdColumnName('pid');
                $dataMap->setLanguageIdColumnName('lid');
                return $dataMap;
        }
    }

    /**
     * @throws RuntimeException
     * @param string $className
     * @param string $propertyName
     * @return string
     */
    public function getTargetEntity($className, $propertyName)
    {

    }

    /**
     * Get a Doctrine2 Type from a TCA Column Type
     *
     * @param string $tableName
     * @param string $columnName
     * @return string
     */
    public function getTCAColumnType($tableName, $columnName)
    {

    }
}
