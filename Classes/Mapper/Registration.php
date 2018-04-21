<?php

/**
 * This class represents a mapper for registrations.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_Mapper_Registration extends Tx_Oelib_DataMapper
{
    /**
     * @var string the name of the database table for this mapper
     */
    protected $tableName = 'tx_seminars_attendances';

    /**
     * @var string the model class name for this mapper, must not be empty
     */
    protected $modelClassName = Tx_Seminars_Model_Registration::class;

    /**
     * @var string[] the (possible) relations of the created models in the format DB column name => mapper name
     */
    protected $relations = [
        'seminar' => Tx_Seminars_Mapper_Event::class,
        'user' => Tx_Seminars_Mapper_FrontEndUser::class,
        'currency' => Tx_Oelib_Mapper_Currency::class,
        'method_of_payment' => Tx_Seminars_Mapper_PaymentMethod::class,
        'lodgings' => Tx_Seminars_Mapper_Lodging::class,
        'foods' => Tx_seminars_Mapper_Food::class,
        'checkboxes' => Tx_Seminars_Mapper_Checkbox::class,
        'additional_persons' => Tx_Seminars_Mapper_FrontEndUser::class,
    ];
}
