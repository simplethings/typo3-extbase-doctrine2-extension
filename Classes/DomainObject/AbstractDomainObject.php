<?php

/**
 * @MappedSuperclass
 */
abstract class Tx_Doctrine2_DomainObject_AbstractDomainObject
{
    /**
     * @var int The uid of the record. The uid is only unique in the context of the database table.
     */
    protected $uid;

    /**
     * @var int The uid of the localized record. In TYPO3 v4.x the property "uid" holds the uid of the record in default language (the translationOrigin).
     */
    protected $localizedUid;

    /**
     * @var int The uid of the language of the object. In TYPO3 v4.x this is the uid of the language record in the table sys_language.
     */
    protected $languageUid;

    /**
     * @var int The id of the page the record is "stored".
     */
    protected $pid;

    /**
     * This is the magic __wakeup() method. It's invoked by the unserialize statement in the reconstitution process
     * of the object. If you want to implement your own __wakeup() method in your Domain Object you have to call
     * parent::__wakeup() first!
     *
     * @return void
     */
    public function __wakeup()
    {
        $this->initializeObject();
    }

    public function initializeObject()
    {
    }

    /**
     * Getter for uid.
     *
     * @return int the uid or NULL if none set yet.
     */
    final public function getUid()
    {
        if ($this->uid !== NULL) {
            return (int)$this->uid;
        } else {
            return NULL;
        }
    }

    /**
     * Setter for the pid.
     *
     * @return void
     */
    public function setPid($pid)
    {
        if ($pid === NULL) {
            $this->pid = NULL;
        } else {
            $this->pid = (int)$pid;
        }
    }

    /**
     * Getter for the pid.
     *
     * @return int The pid or NULL if none set yet.
     */
    public function getPid()
    {
        if ($this->pid === NULL) {
            return NULL;
        } else {
            return (int)$this->pid;
        }
    }

    /**
     * Returns the class name and the uid of the object as string
     *
     * @return string
     */
    public function __toString()
    {
        return get_class($this) . ':' . (string)$this->uid;
    }
}

