<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\ViewHelpers;

use OliverKlee\Seminars\ViewHelpers\RichTextViewHelper;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Seminars\ViewHelpers\RichTextViewHelper
 */
final class RichTextViewHelperTest extends UnitTestCase
{
    protected bool $resetSingletonInstances = true;

    /**
     * @var RichTextViewHelper
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new RichTextViewHelper();
    }

    /**
     * @test
     */
    public function wrapsPlainTextInParagraph(): void
    {
        $result = $this->subject->render('This is plain text.');

        self::assertSame('<p>This is plain text.</p>', $result);
    }

    /**
     * @test
     */
    public function rendersAllowedTagsUnchanged(): void
    {
        $result = $this->subject->render('<p><b>bold text</b></p>');

        self::assertSame('<p><b>bold text</b></p>', $result);
    }

    /**
     * @test
     */
    public function discardsStrayClosingTag(): void
    {
        $result = $this->subject->render('<p>bold text</b></p>');

        self::assertSame('<p>bold text</p>', $result);
    }

    /**
     * @test
     */
    public function encodesUnknownTag(): void
    {
        $result = $this->subject->render('<p><coffee>bold text</coffee></p>');

        $expected = '<p>' . \htmlspecialchars('<coffee>bold text</coffee>', ENT_QUOTES | ENT_HTML5) . '</p>';
        self::assertSame($expected, $result);
    }
}
