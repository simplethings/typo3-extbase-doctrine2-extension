<?php

/**
 * Extbase Bootstrap that disables the evil persist all.
 */
class Tx_Doctrine2_ExtbaseBootstrap extends Tx_Extbase_Core_Bootstrap
{

    /**
     * Flag if autoloading is bootstrapped.
     *
     * @var bool
     */
    static protected $bootstrapped = false;

    static public function bootstrapAutoloading()
    {
        if (!self::$bootstrapped) {
            self::$bootstrapped = true;

            require_once __DIR__ . '/../vendor/doctrine-orm/lib/Doctrine/ORM/Tools/Setup.php';
            \Doctrine\ORM\Tools\Setup::registerAutoloadGit(__DIR__ . '/../vendor/doctrine-orm/');

            \Doctrine\Common\Annotations\AnnotationRegistry::registerFile(__DIR__ . "/../vendor/doctrine-orm/lib/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php");
        }
    }
    /**
     * Resets global singletons for the next plugin
     *
     * @return void
     */
    protected function resetSingletons()
    {
        $this->reflectionService->shutdown();
    }

    public function initializePersistence()
    {
        self::bootstrapAutoloading();
        $this->persistenceManager = $this->objectManager->get('Tx_Extbase_Persistence_ManagerInterface'); // singleton
    }

    protected function initializeBackwardsCompatibility()
    {
    }
}
