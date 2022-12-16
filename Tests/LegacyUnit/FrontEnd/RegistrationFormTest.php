<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\FrontEnd;

use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\Seminars\FrontEnd\RegistrationForm;
use OliverKlee\Seminars\Model\Event;
use OliverKlee\Seminars\OldModel\LegacyEvent;
use OliverKlee\Seminars\Service\RegistrationManager;
use OliverKlee\Seminars\Tests\Functional\Traits\LanguageHelper;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * @covers \OliverKlee\Seminars\FrontEnd\AbstractEditor
 * @covers \OliverKlee\Seminars\FrontEnd\RegistrationForm
 */
final class RegistrationFormTest extends TestCase
{
    use LanguageHelper;

    /**
     * @var RegistrationForm
     */
    private $subject;

    /**
     * @var TestingFramework
     */
    private $testingFramework;

    /**
     * @var int the UID of the event the fixture relates to
     */
    private $seminarUid = 0;

    /**
     * @var LegacyEvent
     */
    private $seminar;

    protected function setUp(): void
    {
        if ((new Typo3Version())->getMajorVersion() >= 11) {
            self::markTestSkipped('Skipping because this code will be removed before adding 11LTS compatibility.');
        }

        $this->testingFramework = new TestingFramework('tx_seminars');
        $rootPageUid = $this->testingFramework->createFrontEndPage();
        $this->testingFramework->changeRecord('pages', $rootPageUid, ['slug' => '/home']);
        $this->testingFramework->createFakeFrontEnd($rootPageUid);

        $configurationRegistry = ConfigurationRegistry::getInstance();
        $configuration = new DummyConfiguration();
        $configuration->setAsString('currency', 'EUR');
        $configurationRegistry->set('plugin.tx_seminars', $configuration);
        $infoTablesConfiguration = new DummyConfiguration();
        $configurationRegistry->set('plugin.tx_staticinfotables_pi1', $infoTablesConfiguration);

        $this->seminar = new LegacyEvent(
            $this->testingFramework->createRecord(
                'tx_seminars_seminars',
                ['payment_methods' => '1']
            )
        );
        $this->seminarUid = $this->seminar->getUid();

        $this->subject = new RegistrationForm(
            [
                'pageToShowAfterUnregistrationPID' => $rootPageUid,
                'sendParametersToThankYouAfterRegistrationPageUrl' => 1,
                'thankYouAfterRegistrationPID' => $rootPageUid,
                'sendParametersToPageToShowAfterUnregistrationUrl' => 1,
                'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
                'showRegistrationFields' => 'registered_themselves,attendees_names',
                'showFeUserFieldsInRegistrationForm' => 'name,email',
                'showFeUserFieldsInRegistrationFormWithLabel' => 'email',
                'form.' => [
                    'unregistration.' => [],
                    'registration.' => [
                        'step1.' => [],
                        'step2.' => [],
                    ],
                ],
            ],
            $this->getFrontEndController()->cObj
        );
        $this->subject->setAction('register');
        $this->subject->setSeminar($this->seminar);
        $this->subject->setTestMode();
    }

    protected function tearDown(): void
    {
        if ($this->testingFramework instanceof TestingFramework) {
            $this->testingFramework->cleanUp();
        }

        RegistrationManager::purgeInstance();
    }

    private function getFrontEndController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }

    // Test concerning getAllFeUserData

    /**
     * @test
     */
    public function getAllFeUserContainsNonEmptyNameOfFrontEndUser(): void
    {
        $this->testingFramework->createAndLoginFrontEndUser('', ['name' => 'John Doe']);

        self::assertStringContainsString(
            'John Doe',
            $this->subject->getAllFeUserData()
        );
    }

    /**
     * @test
     */
    public function getAllFeUserContainsLabelForNonEmptyEmailOfFrontEndUser(): void
    {
        $this->testingFramework->createAndLoginFrontEndUser('', ['email' => 'john@example.com']);

        self::assertStringContainsString(
            'mail',
            $this->subject->getAllFeUserData()
        );
    }

    /**
     * @test
     */
    public function getAllFeUserDoesNotContainEmptyLinesForMissingCompanyName(): void
    {
        $this->testingFramework->createAndLoginFrontEndUser('', ['name' => 'John Doe']);

        self::assertNotRegExp(
            '/<br *\\/>\\s*<br *\\/>/',
            $this->subject->getAllFeUserData()
        );
    }

    /**
     * @test
     */
    public function getAllFeUserContainsNoUnreplacedMarkers(): void
    {
        $this->testingFramework->createAndLoginFrontEndUser('', ['name' => 'John Doe']);

        self::assertStringNotContainsString(
            '###',
            $this->subject->getAllFeUserData()
        );
    }

    ////////////////////////////////////////
    // Tests concerning isFormFieldEnabled
    ////////////////////////////////////////

    /**
     * Data provider that returns the keys of all available form fields.
     *
     * @return array[] two-dimensional array with the inner array being:
     *               [key] string: the form field key
     *               [self-contained] boolean: whether the field is visible
     *                                if no other fields are visible
     *
     * @see isFormFieldEnabledForNoFieldsEnabledReturnsFalseForEachField
     * @see isFormFieldEnabledForNoFieldsEnabledReturnsTrueForSelfContainedFields
     */
    public function formFieldsDataProvider(): array
    {
        return [
            'price' => [
                'key' => 'price',
                'self-contained' => true,
            ],
            'method_of_payment' => [
                'key' => 'method_of_payment',
                'self-contained' => false,
            ],
            'billing_address' => [
                'key' => 'billing_address',
                'self-contained' => false,
            ],
            'company' => [
                'key' => 'company',
                'self-contained' => true,
            ],
            'gender' => [
                'key' => 'gender',
                'self-contained' => true,
            ],
            'name' => [
                'key' => 'name',
                'self-contained' => true,
            ],
            'address' => [
                'key' => 'address',
                'self-contained' => true,
            ],
            'zip' => [
                'key' => 'zip',
                'self-contained' => true,
            ],
            'city' => [
                'key' => 'city',
                'self-contained' => true,
            ],
            'country' => [
                'key' => 'country',
                'self-contained' => true,
            ],
            'telephone' => [
                'key' => 'telephone',
                'self-contained' => true,
            ],
            'email' => [
                'key' => 'email',
                'self-contained' => true,
            ],
            'interests' => [
                'key' => 'interests',
                'self-contained' => true,
            ],
            'expectations' => [
                'key' => 'expectations',
                'self-contained' => true,
            ],
            'background_knowledge' => [
                'key' => 'background_knowledge',
                'self-contained' => true,
            ],
            'accommodation' => [
                'key' => 'accommodation',
                'self-contained' => true,
            ],
            'food' => [
                'key' => 'food',
                'self-contained' => true,
            ],
            'known_from' => [
                'key' => 'known_from',
                'self-contained' => true,
            ],
            'seats' => [
                'key' => 'seats',
                'self-contained' => true,
            ],
            'registered_themselves' => [
                'key' => 'registered_themselves',
                'self-contained' => true,
            ],
            'attendees_names' => [
                'key' => 'attendees_names',
                'self-contained' => true,
            ],
            'kids' => [
                'key' => 'kids',
                'self-contained' => true,
            ],
            'lodgings' => [
                'key' => 'lodgings',
                'self-contained' => false,
            ],
            'foods' => [
                'key' => 'foods',
                'self-contained' => false,
            ],
            'checkboxes' => [
                'key' => 'checkboxes',
                'self-contained' => false,
            ],
            'notes' => [
                'key' => 'notes',
                'self-contained' => true,
            ],
            'total_price' => [
                'key' => 'total_price',
                'self-contained' => true,
            ],
            'feuser_data' => [
                'key' => 'feuser_data',
                'self-contained' => true,
            ],
            'registration_data' => [
                'key' => 'registration_data',
                'self-contained' => true,
            ],
            'terms' => [
                'key' => 'terms',
                'self-contained' => true,
            ],
            'terms_2' => [
                'key' => 'terms_2',
                'self-contained' => false,
            ],
        ];
    }

    /**
     * @test
     *
     * @param string $key the key of the field to check for, must not be empty
     *
     * @dataProvider formFieldsDataProvider
     */
    public function isFormFieldEnabledForNoFieldsEnabledReturnsFalseForEachField(string $key): void
    {
        $subject = new RegistrationForm(
            ['showRegistrationFields' => ''],
            $this->getFrontEndController()->cObj
        );

        $event = $this->createMock(LegacyEvent::class);
        $subject->setSeminar($event);

        self::assertFalse(
            $subject->isFormFieldEnabled($key)
        );
    }

    /**
     * @test
     *
     * @param string $key the key of the field to check for, must not be empty
     * @param bool $isSelfContained
     *        whether the field will be visible if no other fields are enabled
     *        and the event has no special features enabled
     *
     * @dataProvider formFieldsDataProvider
     */
    public function isFormFieldEnabledForNoFieldsEnabledReturnsTrueForSelfContainedFields(
        string $key,
        bool $isSelfContained
    ): void {
        $subject = new RegistrationForm(
            ['showRegistrationFields' => $key],
            $this->getFrontEndController()->cObj
        );
        $event = $this->createMock(LegacyEvent::class);
        $subject->setSeminar($event);

        self::assertEquals(
            $isSelfContained,
            $subject->isFormFieldEnabled($key)
        );
    }

    /**
     * @test
     */
    public function isFormFieldEnabledForEnabledRegisteredThemselvesFieldOnlyReturnsFalseForMoreSeats(): void
    {
        $subject = new RegistrationForm(
            ['showRegistrationFields' => 'registered_themselves'],
            $this->getFrontEndController()->cObj
        );

        self::assertFalse(
            $subject->isFormFieldEnabled('more_seats')
        );
    }

    /**
     * @test
     */
    public function isFormFieldEnabledForEnabledCompanyFieldReturnsTrueForBillingAddress(): void
    {
        $subject = new RegistrationForm(
            ['showRegistrationFields' => 'company, billing_address'],
            $this->getFrontEndController()->cObj
        );

        self::assertTrue(
            $subject->isFormFieldEnabled('billing_address')
        );
    }

    /////////////////////////////////////////
    // Tests concerning getRegistrationData
    /////////////////////////////////////////

    /**
     * @test
     */
    public function getRegistrationDataForDisabledPaymentMethodFieldReturnsEmptyString(): void
    {
        $selectedPaymentMethodUid = $this->testingFramework->createRecord(
            'tx_seminars_payment_methods',
            ['title' => 'payment foo']
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_payment_methods_mm',
            $this->seminarUid,
            $selectedPaymentMethodUid
        );
        $this->subject->setFakedFormValue(
            'method_of_payment',
            $selectedPaymentMethodUid
        );

        self::assertEquals(
            '',
            $this->subject->getAllRegistrationDataForConfirmation()
        );
    }

    /**
     * @test
     */
    public function getRegistrationDataHtmlspecialcharsInterestsField(): void
    {
        $subject = new RegistrationForm(
            [
                'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
                'showRegistrationFields' => 'interests',
            ],
            $this->getFrontEndController()->cObj
        );
        $subject->setTestMode();

        $event = new LegacyEvent($this->seminarUid);
        $subject->setSeminar($event);
        $subject->setFakedFormValue('interests', 'A, B & C');

        self::assertStringContainsString(
            'A, B &amp; C',
            $subject->getAllRegistrationDataForConfirmation()
        );
    }

    /**
     * @test
     */
    public function getRegistrationDataReplacesCarriageReturnInInterestsFieldWithBr(): void
    {
        $subject = new RegistrationForm(
            [
                'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
                'showRegistrationFields' => 'interests',
            ],
            $this->getFrontEndController()->cObj
        );
        $subject->setTestMode();

        $event = new LegacyEvent($this->seminarUid);
        $subject->setSeminar($event);
        $subject->setFakedFormValue('interests', "Love\rPeace");

        self::assertStringContainsString(
            'Love<br />Peace',
            $subject->getAllRegistrationDataForConfirmation()
        );
    }

    /**
     * @test
     */
    public function getRegistrationDataCanContainAttendeesNames(): void
    {
        $subject = new RegistrationForm(
            [
                'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
                'showRegistrationFields' => 'attendees_names',
            ],
            $this->getFrontEndController()->cObj
        );
        $subject->setTestMode();

        $event = new LegacyEvent($this->seminarUid);
        $subject->setSeminar($event);
        $subject->setFakedFormValue('attendees_names', 'John Doe');

        self::assertStringContainsString(
            'John Doe',
            $subject->getAllRegistrationDataForConfirmation()
        );
    }

    /**
     * @test
     */
    public function getRegistrationDataForAttendeesNamesAndThemselvesSelectedContainsUserName(): void
    {
        $this->testingFramework->createAndLoginFrontEndUser(
            '',
            ['name' => 'Jane Doe']
        );

        $subject = new RegistrationForm(
            [
                'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
                'showRegistrationFields' => 'attendees_names,registered_themselves',
            ],
            $this->getFrontEndController()->cObj
        );
        $subject->setTestMode();

        $event = new LegacyEvent($this->seminarUid);
        $subject->setSeminar($event);
        $subject->setFakedFormValue('attendees_names', 'John Doe');
        $subject->setFakedFormValue('registered_themselves', '1');

        self::assertStringContainsString(
            'Jane Doe',
            $subject->getAllRegistrationDataForConfirmation()
        );
    }

    /**
     * @test
     */
    public function getRegistrationDataForAttendeesNamesEnabledAndThemselvesNotSelectedNotContainsUserName(): void
    {
        $this->testingFramework->createAndLoginFrontEndUser(
            '',
            ['name' => 'Jane Doe']
        );

        $subject = new RegistrationForm(
            [
                'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
                'showRegistrationFields' => 'attendees_names,registered_themselves',
            ],
            $this->getFrontEndController()->cObj
        );
        $subject->setTestMode();

        $event = new LegacyEvent($this->seminarUid);
        $subject->setSeminar($event);
        $subject->setFakedFormValue('attendees_names', 'John Doe');
        $subject->setFakedFormValue('registered_themselves', '');

        self::assertStringNotContainsString(
            'Jane Doe',
            $subject->getAllRegistrationDataForConfirmation()
        );
    }

    // Tests concerning getSeminar and getEvent

    /**
     * @test
     */
    public function getSeminarReturnsSeminarFromSetSeminar(): void
    {
        self::assertSame(
            $this->seminar,
            $this->subject->getSeminar()
        );
    }

    /**
     * @test
     */
    public function getEventReturnsEventWithSeminarUid(): void
    {
        $event = $this->subject->getEvent();
        self::assertInstanceOf(
            Event::class,
            $event
        );

        self::assertSame(
            $this->seminarUid,
            $event->getUid()
        );
    }
}
