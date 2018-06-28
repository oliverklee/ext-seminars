<?php

/**
 * This class represents a sender
 *
 * @author Sascha Maier <sam@amedick-sommer.de>
 * @author Bernd Schönbach <bernd@oliverklee.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Seminars_Model_Sender implements \Tx_Oelib_Interface_MailRole
{

    /**
     * @var string the name of the sender
     */
    protected $name;

    /**
     * @var string the e-mail of the sender
     */
    protected $emailAddress;

    /**
     * Tx_Seminars_Model_Sender constructor.
     * @param string $name
     * @param string $emailAddress
     */
    public function __construct(string $name, string $emailAddress)
    {
        $this->name = $name;
        $this->emailAddress = $emailAddress;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @param string $emailAddress
     */
    public function setEmailAddress(string $emailAddress): void
    {
        $this->emailAddress = $emailAddress;
    }

    /**
     * Returns the real name of the e-mail role.
     *
     * @return string the real name of the e-mail role, might be empty
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Returns the e-mail address of the e-mail role.
     *
     * @return string the e-mail address of the e-mail role, might be empty
     */
    public function getEmailAddress() {
        return $this->emailAddress;
    }
}
