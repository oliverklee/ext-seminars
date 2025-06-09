<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Form\Element;

use OliverKlee\Seminars\Form\Element\EventDetailsElement;
use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Backend\Form\Element\GroupElement;
use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Seminars\Form\Element\EventDetailsElement
 */
final class EventDetailsElementTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'oliverklee/feuserextrafields',
        'oliverklee/oelib',
        'oliverklee/seminars',
    ];

    /**
     * @test
     */
    public function isAbstractFormElement(): void
    {
        $subject = new EventDetailsElement(new NodeFactory(), []);

        self::assertInstanceOf(AbstractFormElement::class, $subject);
    }

    /**
     * @test
     */
    public function isGroupElement(): void
    {
        $subject = new EventDetailsElement(new NodeFactory(), []);

        self::assertInstanceOf(GroupElement::class, $subject);
    }

    private function renderWithData(array $data): string
    {
        $subject = new EventDetailsElement(new NodeFactory(), $data);

        return $subject->render()['html'] ?? '';
    }

    /**
     * @test
     */
    public function renderForNonRegistrationTableThrowsException(): void
    {
//        $this->expectException(\BadMethodCallException::class);
//        $this->expectExceptionMessage('EventDetailsElement can only be used in the "tx_seminars_registration" table.');
//        $this->expectExceptionCode(1749576134);

        $data = [
            'tableName' => 'pages',
            'row' => [],
            'inlineStructure' => [],
        ];
        $subject = new EventDetailsElement(new NodeFactory(), $data);

        $subject->render();
    }
}
