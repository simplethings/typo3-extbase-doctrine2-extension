<?php

use Doctrine\ORM\Mapping\ClassMetadata;

class Tx_Doctrine2_Tests_Mapping_TYPO3ExtbaseDriverTest extends Tx_Doctrine2_Tests_TestCase
{
    private $metadataService;
    private $driver;

    public function setUp()
    {
        $this->metadataService = $this->getMock('Tx_Doctrine2_Mapping_TYPO3MetadataService', array('getDataMap', 'getTCAColumnType', 'getTargetEntity'));
        $this->driver = new Tx_Doctrine2_Mapping_TYPO3ExtbaseDriver();
        $this->driver->injectMetadataService($this->metadataService);
    }

    public function testLoadMetadataForAbstractDomainObject()
    {
        $metadata = new ClassMetadata('Tx_Extbase_DomainObject_AbstractDomainObject');
        $this->driver->loadMetadataForClass('Tx_Extbase_DomainObject_AbstractDomainObject', $metadata);

        $this->assertEquals(array('uid'), $metadata->identifier);
        $this->assertEquals(array('uid' => 'uid'), $metadata->fieldNames);
    }

    public function testLoadMetadataForSimpleClass()
    {
        $dataMap = new Tx_Extbase_Persistence_Mapper_DataMap('Tx_Doctrine2_Tests_Model_Post', 'posts');
        $dataMap->setPageIdColumnName('pid');
        $dataMap->setLanguageIdColumnName('lid');

        $this->metadataService->expects($this->once())->method('getDataMap')->will($this->returnValue($dataMap));

        $metadata = new ClassMetadata('Tx_Doctrine2_Tests_Model_Post');
        $this->driver->loadMetadataForClass('Tx_Doctrine2_Tests_Model_Post', $metadata);

        $this->assertEquals('posts', $metadata->getTableName());
        $this->assertEquals(array(), $metadata->identifier, 'No identifier, this this inherited from abstract class');
        $this->assertEquals(array('pid' => 'pid', 'lid' => '_languageUid'), $metadata->fieldNames);
    }

    public function testLoadMetadataForClassWithProperties()
    {
        $dataMap = new Tx_Extbase_Persistence_Mapper_DataMap('Tx_Doctrine2_Tests_Model_Post', 'posts');
        $column = new Tx_Extbase_Persistence_Mapper_ColumnMap('post_headline', 'headline');
        $column->setTypeOfRelation(Tx_Extbase_Persistence_Mapper_ColumnMap::RELATION_NONE);
        $dataMap->addColumnMap($column);

        $this->metadataService->expects($this->once())->method('getDataMap')->will($this->returnValue($dataMap));

        $metadata = new ClassMetadata('Tx_Doctrine2_Tests_Model_Post');
        $this->driver->loadMetadataForClass('Tx_Doctrine2_Tests_Model_Post', $metadata);

        $this->assertEquals('posts', $metadata->getTableName());
        $this->assertEquals(array(), $metadata->identifier, 'No identifier, this this inherited from abstract class');
        $this->assertEquals(array('post_headline' => 'headline'), $metadata->fieldNames);

    }
}
