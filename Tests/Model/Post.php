<?php

/**
 * @Entity
 */
class Tx_Doctrine2_Tests_Model_Post extends Tx_Doctrine2_DomainObject_AbstractDomainObject
{
    /**
     * @var string
     */
    protected $headline;
    /**
     * @var string
     */
    protected $body;

    /**
     * @var Tx_Doctrine2_Tests_Model_User
     */
    protected $author;

    /**
     * @var Tx_Extbase_Persistence_ObjectStorage<Tx_Doctrine2_Tests_Model_Comments>
     */
    protected $comments;

    /**
     * Get headline.
     *
     * @return headline.
     */
    public function getHeadline()
    {
        return $this->headline;
    }

    /**
     * Set headline.
     *
     * @param headline the value to set.
     */
    public function setHeadline($headline)
    {
        $this->headline = $headline;
    }

    /**
     * Get body.
     *
     * @return body.
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set body.
     *
     * @param body the value to set.
     */
    public function setBody($body)
    {
        $this->body = $body;
    }
}

