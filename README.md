# Extbase ORM Doctrine 2

This TYPO3 4.6+ extension completly replaces the Extbase ORM with Doctrine2.

    Note: This extension is in very early stage and might not live up to the "completly" phrase yet.

## Want to Have Features

* 100% implementation of Extbase Persistence API
* Access to Doctrine2 APIs for more powerful queries, more performant access to objects.
* Support for the extbase mapping syntax, so no rewriting of existing code necessary (hopefully).

## Implementation Details

### Mapping Driver

We need a mapping driver that works with the following assumptions

* Mapping TCA/@var types to the underlying database types of Persistence API
* Mapping `Tx_Extbase_Persistence_ObjectStorage` as collections of objects.
* Mapping @lazy and @cascade
* Classes have to extend `Tx_Extbase_DomainObject_AbstractEntity`.
* Add Mapping type for unix timestamps (ieks) to DateTime
* Enable Fields will be implemented using Doctrine Filters

### AbstractDomainObject

All Doctrine entities have to extend `AbstractDomainObject` or `AbstractEntity`.

By TYPO3 convention every table has at least a 'uid' that is mapped by default by these classes.

### Collection Abstraction

Extbase ORM uses the `Tx_Extbase_Persistence_ObjectStorge` as collection for objects. This is not compatible with Doctrine collections that have a very different API. What is possible is to automatically convert ObjectStorages into Doctrine Collections upon prePersist. However Doctrine creates collections when reconstiution objects back from the database that don't impelement the interface of Extbase. That means calls of the type `$this->objects->attach()` and `$this->objects->detach()` as others will fail.

A solution is to extend Doctrine to allow subclasses of PersistentCollection to be returned from Doctrine internals.

### IdentityMap

The IdentityMap has to be replaced by an objct that behaves the same.

### Interfaces and Implementations

* `Tx_Extbase_Persistence_Manager` is generic and can stay.
* `Tx_Extbase_Persistence_BackendInterface` will be swaped by a DoctrineBackend implementation. The Storage namespace is not necessary anymore.
* `Tx_Extbase_Persistence_QueryInterface` will be swaped aswell as QueryResultInterface, QueryFactoryInterface and all Logical Operators
* `Tx_Extbase_Persistence_Session` has to be kept up to Date by Doctrine.
* `Tx_Extbase_Persistence_Repository` will be implemented by lots of third party code. This has to run (even if inefficent) with Doctrine flawlessly. A second implementation will be provided for more efficient access called `DoctrineRepository`.

## Notes

* This extension follows Doctrine coding standards. Bite me :-)
* For tests run "git submodule update --init", then "phpunit" from the main directory. Non-extbase TYPO3 code will be re-implemented as real stubs.

