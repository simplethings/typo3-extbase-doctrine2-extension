<?php

/**
 * Extbase Bootstrap that disables the evil persist all.
 */
class Tx_Doctrine2_ExtbaseBootstrap extends Tx_Extbase_Core_Bootstrap
{
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
        $this->persistenceManager = $this->objectManager->get('Tx_Doctrine2_Manager'); // singleton
    }

    protected function initializeBackwardsCompatibility()
    {
    }
}
