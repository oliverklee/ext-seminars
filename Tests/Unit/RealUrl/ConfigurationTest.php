<?php
namespace OliverKlee\Seminars\Tests\Unit\RealUrl;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */
use OliverKlee\Seminars\RealUrl\Configuration as RealUrlConfiguration;

/**
 * Testcase.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class ConfigurationTest extends \Tx_Phpunit_TestCase
{
    /**
     * @var RealUrlConfiguration
     */
    protected $subject = null;

    protected function setUp()
    {
        $this->subject = new RealUrlConfiguration();
    }

    /**
     * @test
     */
    public function addConfigurationAddsEventSingleViewPostVarSet()
    {
        $configurationBefore = ['config' => []];

        $configurationAfter = $this->subject->addConfiguration($configurationBefore);

        self::assertSame(
            [
                [
                    'GETvar' => 'tx_seminars_pi1[showUid]',
                    'lookUpTable' => [
                        'table' => 'tx_seminars_seminars',
                        'id_field' => 'uid',
                        'alias_field' => 'title',
                        'addWhereClause' => ' AND NOT deleted',
                        'useUniqueCache' => true,
                        'useUniqueCache_conf' => [
                            'strtolower' => 1,
                            'spaceCharacter' => '-',
                        ],
                        'autoUpdate' => true,
                    ],
                ],
            ],
            $configurationAfter['postVarSets']['_DEFAULT']['event']
        );
    }
}