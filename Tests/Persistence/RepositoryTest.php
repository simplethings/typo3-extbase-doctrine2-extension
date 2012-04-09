<?php

use Doctrine\ORM\Mapping as ORM;

class Tx_Doctrine2_Tests_Persistence_RepositoryTest extends Tx_Doctrine2_Tests_TestCase
{
    private $repository;

    public function setUp()
    {
        parent::setUp();
        $this->repository = new Tx_TestApp_Domain_Repository_PersonRepository(null);
        $this->repository->setEntityManager($this->entityManager);
    }

    public function testGetRepositoryClassName()
    {
        $this->assertEquals('Tx_TestApp_Domain_Model_Person', $this->repository->getObjectType());
    }

    public function testAddInvalidObject()
    {
        $obj = new stdClass;

        $this->setExpectedException('InvalidArgumentException', 'Expected object of type Tx_TestApp_Domain_Model_Person but got object of type stdClass');
        $this->repository->add($obj);
    }

    public function testAdd()
    {
        $person = new Tx_TestApp_Domain_Model_Person();
        $this->repository->add($person);

        $this->assertTrue($this->entityManager->contains($person));
        $this->assertTrue(in_array($person, $this->repository->getAddedObjects(), true));
    }

    public function testRemoveInvalidType()
    {
        $obj = new stdClass;

        $this->setExpectedException('InvalidArgumentException', 'Expected object of type Tx_TestApp_Domain_Model_Person but got object of type stdClass');
        $this->repository->remove($obj);
    }

    public function testRemove()
    {
        $person = new Tx_TestApp_Domain_Model_Person();
        $this->repository->remove($person);

        $this->assertTrue(in_array($person, $this->repository->getRemovedObjects(), true));
    }

    public function testFindAll()
    {
        $this->loadFixture();

        $objects = $this->repository->findAll();
        $this->assertInternalType('array', $objects);
        $this->assertEquals(2, count($objects));
    }

    public function testCountAll()
    {
        $this->loadFixture();

        $this->assertEquals(2, $this->repository->countAll());
    }

    public function testFindByUid()
    {
        $uid = $this->loadFixture();

        $person = $this->repository->findByUid($uid);

        $this->assertInstanceOf('Tx_TestApp_Domain_Model_Person', $person);
        $this->assertEquals($uid, $person->getUid());
    }

    public function testCreateQuery()
    {
        $settings = $this->getMock('Tx_Extbase_Persistence_QuerySettingsInterface');

        $this->repository->setDefaultOrderings(array("uid" => "ASC"));
        $this->repository->setDefaultQuerySettings($settings);

        $query = $this->repository->createQuery();

        $this->assertInstanceOf('Tx_Extbase_Persistence_QueryInterface', $query);
        $this->assertEquals(array("uid" => "ASC"), $query->getOrderings());
        $this->assertSame($settings, $query->getQuerySettings());
    }

    public function testCreateDqlQuery()
    {
        $query = $this->repository->createDqlQuery("SELECT p FROM Tx_TestApp_Domain_Model_Person");

        $this->assertInstanceOf('Doctrine\ORM\Query', $query);
    }

    public function testCreateDqlQueryBuilder()
    {
        $qb = $this->repository->createQueryBuilder();

        $this->assertInstanceOf('Doctrine\ORM\QueryBuilder', $qb);
    }

    protected function loadFixture()
    {
        $this->setupDatabase(array('Tx_TestApp_Domain_Model_Person'));

        $person1 = new Tx_TestApp_Domain_Model_Person();
        $person2 = new Tx_TestApp_Domain_Model_Person();
        $this->repository->add($person1);
        $this->repository->add($person2);
        $this->entityManager->flush();
        $this->entityManager->clear();

        return $person1->getUid();
    }
}

class Tx_TestApp_Domain_Repository_PersonRepository extends Tx_Doctrine2_Persistence_Repository
{

}

/**
 * @Entity
 */
class Tx_TestApp_Domain_Model_Person extends Tx_Doctrine2_DomainObject_AbstractEntity
{
    
}
