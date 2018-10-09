<?php
namespace OliverKlee\Seminars\Controller;

/**
 * This file is part of the "seminars" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * Controller of news records
 *
 */
class SeminarController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{

    /**
     * @var \Tx_Seminars_Mapper_Event
     */
    protected $eventRepository;


    /**
     * @param \Tx_Seminars_Mapper_Event $eventRepository
     */
    public function injectEventRepository(\Tx_Seminars_Mapper_Event $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }


    /**
     * action list
     *
     * @return void
     */
    public function listAction() {

        $events = $this->eventRepository->findAllByBeginDate(0, 2147483647);

        $this->view->assign('events', $events);

    }


}
