<?php
namespace OliverKlee\Seminars\RealUrl;

/**
 * This class adds a RealURL configuration.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Configuration
{
    /**
     * Adds RealURL configuration.
     *
     * @param mixed[][] $parameters the RealUrl configuration to modify
     *
     * @return mixed[][] the modified RealURL configuration
     */
    public function addConfiguration(array $parameters)
    {
        $eventSingleViewPostVariables = [
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
        ];

        return array_merge_recursive(
            $parameters['config'],
            [
                'postVarSets' => [
                    '_DEFAULT' => [
                        'event' => [
                            $eventSingleViewPostVariables,
                        ],
                    ],
                ],
            ]
        );
    }
}
