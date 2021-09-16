<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Model;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Email\SystemEmailFromBuilder;
use OliverKlee\Seminars\Model\Interfaces\Titled;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class EventTest extends UnitTestCase
{
    /**
     * @var \Tx_Seminars_Model_Event
     */
    private $subject = null;

    /**
     * @var \Tx_Seminars_Model_Organizer
     */
    private $organizer = null;

    protected function setUp()
    {
        $this->organizer = new \Tx_Seminars_Model_Organizer();
        $this->organizer->setData(
            [
                'title' => 'Brain Gourmets',
                'email' => 'organizer@example.com',
                'email_footer' => 'Best workshops in town!',
            ]
        );
        $organizers = new Collection();
        $organizers->add($this->organizer);

        $this->subject = new \Tx_Seminars_Model_Event();
        $this->subject->setData(
            [
                'title' => 'A nice event',
                'begin_date' => mktime(10, 0, 0, 4, 8, 2020),
                'end_date' => mktime(18, 30, 0, 4, 20, 2020),
                'registrations' => new Collection(),
                'organizers' => $organizers,
            ]
        );
    }

    /**
     * @test
     */
    public function isTitled()
    {
        self::assertInstanceOf(Titled::class, $this->subject);
    }

    /**
     * @test
     */
    public function getEmailSenderReturnsSystemEmailMailRole()
    {
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = 'system-foo@example.com';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = 'Mr. Default';
        $systemEmailFromBuilder = GeneralUtility::makeInstance(SystemEmailFromBuilder::class);

        self::assertEquals(
            $systemEmailFromBuilder->build(),
            $this->subject->getEmailSender()
        );
    }

    /**
     * @test
     */
    public function getEmailSenderReturnsFirstOrganizerMailRole()
    {
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = '';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = '';

        self::assertEquals(
            $this->organizer,
            $this->subject->getEmailSender()
        );
    }
}
