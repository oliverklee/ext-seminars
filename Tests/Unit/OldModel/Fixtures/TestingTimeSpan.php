<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\OldModel\Fixtures;

/**
 * This class represents a test object from the database.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
final class TestingTimeSpan extends \Tx_Seminars_OldModel_AbstractTimeSpan
{
    /**
     * @var bool whether to call `TemplateHelper::init()` during construction in BE mode
     */
    protected $needsTemplateHelperInitialization = false;

    /**
     * Gets our place(s) as plain text (just the places name).
     * Returns a localized string "will be announced" if the time slot has no
     * place set.
     *
     * @return string our places or an empty string if the timespan has no places
     */
    public function getPlaceShort(): string
    {
        return 'the places';
    }
}
