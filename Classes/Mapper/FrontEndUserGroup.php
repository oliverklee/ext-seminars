<?php

/**
 * This class represents a mapper for front-end user groups.
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Seminars_Mapper_FrontEndUserGroup extends Tx_Oelib_Mapper_FrontEndUserGroup
{
    /**
     * @var string the model class name for this mapper, must not be empty
     */
    protected $modelClassName = Tx_Seminars_Model_FrontEndUserGroup::class;

    /**
     * @var string[] the (possible) relations of the created models in the format DB column name => mapper name
     */
    protected $relations = [
        'tx_seminars_reviewer' => Tx_Oelib_Mapper_BackEndUser::class,
        'tx_seminars_default_categories' => Tx_Seminars_Mapper_Category::class,
        'tx_seminars_default_organizer' => Tx_Seminars_Mapper_Organizer::class,
    ];
}
