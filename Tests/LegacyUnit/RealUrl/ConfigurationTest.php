<?php
declare(strict_types = 1);
namespace OliverKlee\Seminars\Tests\LegacyUnit\RealUrl;

use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\RealUrl\Configuration as RealUrlConfiguration;

/**
 * Testcase.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class ConfigurationTest extends TestCase
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
