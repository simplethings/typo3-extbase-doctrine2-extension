<?php

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;

class Tx_Doctrine2_Tests_Mapping_TYPO3TCAMetadataListenerTest extends Tx_Doctrine2_Tests_TestCase
{
    private $metadataService;
    private $listener;

    public function setUp()
    {
        parent::setUp();

        $this->metadataService = $this->getMock('Tx_Doctrine2_Mapping_TYPO3MetadataService', array('getDataMap', 'getTCAColumnType', 'getTargetEntity'));
        $this->listener = new Tx_Doctrine2_Mapping_TYPO3TCAMetadataListener();
        $this->listener->injectMetadataService($this->metadataService);
    }

    public function loadClassMetadata($className)
    {
        $metadata = new ClassMetadata($className);
        $args = new LoadClassMetadataEventArgs($metadata, $this->entityManager);
        $this->listener->loadClassMetadata($args);
        return $metadata;
    }

    public function testLoadMetadataForAbstractDomainObject()
    {
        $metadata = $this->loadClassMetadata('Tx_Doctrine2_DomainObject_AbstractDomainObject');

        $this->assertEquals(array('uid'), $metadata->identifier);
        $this->assertEquals(array('uid' => 'uid'), $metadata->fieldNames);
    }

    public function testLoadMetadataForSimpleClass()
    {
        $dataMap = new Tx_Extbase_Persistence_Mapper_DataMap('Tx_Doctrine2_Tests_Model_Post', 'posts');
        $dataMap->setPageIdColumnName('pid');
        $dataMap->setLanguageIdColumnName('lid');

        $this->metadataService->expects($this->once())->method('getDataMap')->will($this->returnValue($dataMap));

        $metadata = $this->loadClassMetadata('Tx_Doctrine2_Tests_Model_Post');

        $this->assertEquals('posts', $metadata->getTableName());
        $this->assertEquals(array(), $metadata->identifier, 'No identifier, this this inherited from abstract class');
        $this->assertEquals(array('pid' => 'pid', 'lid' => 'languageUid'), $metadata->fieldNames);
    }

    public function testLoadMetadataForClassWithProperties()
    {
        $dataMap = new Tx_Extbase_Persistence_Mapper_DataMap('Tx_Doctrine2_Tests_Model_Post', 'posts');
        $column = new Tx_Extbase_Persistence_Mapper_ColumnMap('post_headline', 'headline');
        $column->setTypeOfRelation(Tx_Extbase_Persistence_Mapper_ColumnMap::RELATION_NONE);
        $dataMap->addColumnMap($column);

        $this->metadataService->expects($this->once())->method('getDataMap')->will($this->returnValue($dataMap));

        $metadata = $this->loadClassMetadata('Tx_Doctrine2_Tests_Model_Post');

        $this->assertEquals('posts', $metadata->getTableName());
        $this->assertEquals(array(), $metadata->identifier, 'No identifier, this this inherited from abstract class');
        $this->assertEquals(array('post_headline' => 'headline'), $metadata->fieldNames);

    }
}
