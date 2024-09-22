<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyFunctional\Model;

use OliverKlee\Seminars\Model\Organizer;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Seminars\Model\Organizer
 */
final class OrganizerTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/static_info_tables',
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/seminars',
    ];

    protected bool $initializeDatabase = false;

    private Organizer $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new Organizer();
    }

    ///////////////////////////////
    // Tests regarding the name.
    ///////////////////////////////

    /**
     * @test
     */
    public function setNameWithEmptyNameThrowsException(): void
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The parameter $name must not be empty.'
        );

        $this->subject->setName('');
    }

    /**
     * @test
     */
    public function setNameSetsName(): void
    {
        $this->subject->setName('Fabulous organizer');

        self::assertEquals(
            'Fabulous organizer',
            $this->subject->getName()
        );
    }

    /**
     * @test
     */
    public function getNameWithNonEmptyNameReturnsName(): void
    {
        $this->subject->setData(['title' => 'Fabulous organizer']);

        self::assertEquals(
            'Fabulous organizer',
            $this->subject->getName()
        );
    }

    /**
     * @test
     */
    public function getTitleWithNonEmptyNameReturnsName(): void
    {
        $this->subject->setData(['title' => 'Fabulous organizer']);

        self::assertEquals(
            'Fabulous organizer',
            $this->subject->getTitle()
        );
    }

    //////////////////////////////////
    // Tests regarding the homepage.
    //////////////////////////////////

    /**
     * @test
     */
    public function getHomepageInitiallyReturnsAnEmptyString(): void
    {
        $this->subject->setData([]);

        self::assertEquals(
            '',
            $this->subject->getHomepage()
        );
    }

    /**
     * @test
     */
    public function getHomepageWithNonEmptyHomepageReturnsHomepage(): void
    {
        $this->subject->setData(['homepage' => 'https://example.com']);

        self::assertEquals(
            'https://example.com',
            $this->subject->getHomepage()
        );
    }

    /**
     * @test
     */
    public function setHomepageSetsHomepage(): void
    {
        $this->subject->setHomepage('https://example.com');

        self::assertEquals(
            'https://example.com',
            $this->subject->getHomepage()
        );
    }

    /**
     * @test
     */
    public function hasHomepageInitiallyReturnsFalse(): void
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasHomepage()
        );
    }

    /**
     * @test
     */
    public function hasHomepageWithNonEmptyHomepageReturnsTrue(): void
    {
        $this->subject->setHomepage('https://example.com');

        self::assertTrue(
            $this->subject->hasHomepage()
        );
    }

    ////////////////////////////////////////
    // Tests regarding the email address.
    ////////////////////////////////////////

    /**
     * @test
     */
    public function setEmailAddressWithEmptyEmailAddressThrowsException(): void
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The parameter $emailAddress must not be empty.'
        );

        $this->subject->setEmailAddress('');
    }

    /**
     * @test
     */
    public function setEmailAddressSetsEmailAddress(): void
    {
        $this->subject->setEmailAddress('mail@example.com');

        self::assertEquals(
            'mail@example.com',
            $this->subject->getEmailAddress()
        );
    }

    /**
     * @test
     */
    public function getEmailAddressWithNonEmptyEmailAddressReturnsEmailAddress(): void
    {
        $this->subject->setData(['email' => 'mail@example.com']);

        self::assertEquals(
            'mail@example.com',
            $this->subject->getEmailAddress()
        );
    }

    ///////////////////////////////////////
    // Tests regarding the email footer.
    ///////////////////////////////////////

    /**
     * @test
     */
    public function getEmailFooterInitiallyReturnsAnEmptyString(): void
    {
        $this->subject->setData([]);

        self::assertEquals(
            '',
            $this->subject->getEmailFooter()
        );
    }

    /**
     * @test
     */
    public function getEmailFooterWithNonEmptyEmailFooterReturnsEmailFooter(): void
    {
        $this->subject->setData(['email_footer' => 'Example Inc.']);

        self::assertEquals(
            'Example Inc.',
            $this->subject->getEmailFooter()
        );
    }

    /**
     * @test
     */
    public function setEmailFooterSetsEmailFooter(): void
    {
        $this->subject->setEmailFooter('Example Inc.');

        self::assertEquals(
            'Example Inc.',
            $this->subject->getEmailFooter()
        );
    }

    /**
     * @test
     */
    public function hasEmailFooterInitiallyReturnsFalse(): void
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasEmailFooter()
        );
    }

    /**
     * @test
     */
    public function hasEmailFooterWithNonEmptyEmailFooterReturnsTrue(): void
    {
        $this->subject->setEmailFooter('Example Inc.');

        self::assertTrue(
            $this->subject->hasEmailFooter()
        );
    }

    /////////////////////////////////////
    // Tests concerning the description
    /////////////////////////////////////

    /**
     * @test
     */
    public function hasDescriptionForOrganizerWithoutDescriptionReturnsFalse(): void
    {
        $this->subject->setData(['description' => '']);

        self::assertFalse(
            $this->subject->hasDescription()
        );
    }

    /**
     * @test
     */
    public function hasDescriptionForOrganizerWithDescriptionReturnsTrue(): void
    {
        $this->subject->setData(['description' => 'foo']);

        self::assertTrue(
            $this->subject->hasDescription()
        );
    }

    /**
     * @test
     */
    public function getDescriptionForOrganizerWithoutDescriptionReturnsEmptyString(): void
    {
        $this->subject->setData(['description' => '']);

        self::assertEquals(
            '',
            $this->subject->getDescription()
        );
    }

    /**
     * @test
     */
    public function getDescriptionForOrganizerWithDescriptionReturnsDescription(): void
    {
        $this->subject->setData(['description' => 'foo']);

        self::assertEquals(
            'foo',
            $this->subject->getDescription()
        );
    }
}
