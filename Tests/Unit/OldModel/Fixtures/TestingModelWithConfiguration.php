<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\OldModel\Fixtures;

use OliverKlee\Seminars\OldModel\AbstractModel;

/**
 * This class represents a test object from the database.
 */
final class TestingModelWithConfiguration extends AbstractModel
{
    /**
     * @var string the name of the SQL table this class corresponds to
     */
    protected static $tableName = 'tx_seminars_test';

    /**
     * @var bool whether to call `TemplateHelper::init()` during construction in BE mode
     */
    protected $needsTemplateHelperInitialization = true;
}
