<?php

/**
 * Keeps the Tx_Extbase_Persistence_Session up to date.
 *
 * @author Benjamin Eberlei <eberlei@simplethings.de>
 */
class Tx_Doctrine2_SessionListener
{
    /**
     * @var Tx_Extbase_Persistence_Session
     */
    protected $session;

    /**
     *
     * Injects the Persistence Session
     *
     * @param Tx_Extbase_Persistence_Session $session The persistence session
     * @return void
     */
    public function injectSession(Tx_Extbase_Persistence_Session $session)
    {
        $this->session = $session;
    }

    public function postLoad($event)
    {
        $this->session->registerReconstitutedObject($event->getEntity());
    }
}

