<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Templating;

use OliverKlee\Oelib\Configuration\ConfigurationProxy;
use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Oelib\Exception\NotFoundException;
use OliverKlee\Seminars\Tests\Unit\Templating\Fixtures\TestingTemplateHelper;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Seminars\Templating\TemplateHelper
 */
final class TemplateHelperTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = [
        'typo3conf/ext/static_info_tables',
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/seminars',
    ];

    protected $initializeDatabase = false;

    /**
     * @var TestingTemplateHelper
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $frontEndControllerMock = $this->createMock(TypoScriptFrontendController::class);
        $frontEndControllerMock->cObj = $this->createMock(ContentObjectRenderer::class);
        $GLOBALS['TSFE'] = $frontEndControllerMock;

        $configuration = new DummyConfiguration(['enableConfigCheck' => true]);
        ConfigurationProxy::setInstance('seminars', $configuration);

        $this->subject = new TestingTemplateHelper([]);
    }

    protected function tearDown(): void
    {
        ConfigurationProxy::purgeInstances();
        parent::tearDown();
    }

    ///////////////////////////////
    // Tests for getting subparts.
    ///////////////////////////////

    /**
     * @test
     */
    public function noSubpartsAndEmptySubpartName(): void
    {
        self::assertSame(
            '',
            $this->subject->getSubpart()
        );
    }

    /**
     * @test
     */
    public function getSubpartWithNotExistingSubpartNameThrowsException(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('$key contained the subpart name "FOOBAR"');
        $this->expectExceptionCode(1632760625);

        $this->subject->getSubpart('FOOBAR');
    }

    /**
     * @test
     */
    public function getCompleteTemplateReturnsCompleteTemplateContent(): void
    {
        $templateCode = "This is a test including\na linefeed.\n";
        $this->subject->processTemplate(
            $templateCode
        );
        self::assertSame(
            $templateCode,
            $this->subject->getSubpart()
        );
    }

    ////////////////////////////////
    // Tests for setting subparts.
    ////////////////////////////////

    /**
     * @test
     */
    public function setNewSubpartNotEmptyGetSubpart(): void
    {
        $this->subject->processTemplate(
            'Some text.'
        );
        $this->subject->setSubpart('MY_SUBPART', 'foo');
        self::assertSame(
            'foo',
            $this->subject->getSubpart('MY_SUBPART')
        );
    }

    ///////////////////////////////////////////////////
    // Tests for getting subparts with invalid names.
    ///////////////////////////////////////////////////

    /**
     * @test
     */
    public function getSubpartWithLowercaseNameIsIgnoredWithUsingLowercase(): void
    {
        $this->subject->processTemplate(
            '<!-- ###my_subpart### -->'
            . 'Some text.'
            . '<!-- ###my_subpart### -->'
        );

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('$key contained the subpart name "my_subpart"');
        $this->expectExceptionCode(1632760625);

        $this->subject->getSubpart('my_subpart');
    }

    /**
     * @test
     */
    public function subpartWithLowercaseNameIsIgnoredWithUsingUppercase(): void
    {
        $this->subject->processTemplate(
            '<!-- ###my_subpart### -->'
            . 'Some text.'
            . '<!-- ###my_subpart### -->'
        );

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('$key contained the subpart name "MY_SUBPART"');
        $this->expectExceptionCode(1632760625);

        $this->subject->getSubpart('MY_SUBPART');
    }

    // Tests for automatically setting labels.

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function setLabelsAfterGetTemplateCodeWithoutTemplatePathDoesNotCrash(): void
    {
        $this->subject->getTemplateCode();
        $this->subject->setLabels();
    }

    // Tests for getting subparts.

    /**
     * @test
     */
    public function getSubpartWithLabelsReturnsVerbatimSubpartWithoutLabels(): void
    {
        $subpartContent = 'Subpart content';
        $templateCode = 'Text before the subpart
            <!-- ###MY_SUBPART### -->'
            . $subpartContent
            . '<!-- ###MY_SUBPART### -->'
            . 'Text after the subpart.';

        $this->subject->processTemplate($templateCode);

        self::assertSame($subpartContent, $this->subject->getSubpartWithLabels('MY_SUBPART'));
    }
}
