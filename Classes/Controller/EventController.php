<?php
namespace OliverKlee\Seminars\Controller;




/**
 * This file is part of the "seminars" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 *
 * @author Andrea Schmuttermair <andrea@schmutt.de>
 */


/**
 * Controller of news records
 *
 */
class EventController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
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

    public function initializeAction() {
       if (isset($this->settings['flexforms']) && is_array($this->settings['flexforms'])) {
            $flexformsSettings = $this->settings['flexforms'];
            //unset($this->settings['flexforms']);
            foreach ($flexformsSettings as $flexformKey => $flexformValue){
                if ((strlen($flexformValue) > 0 || (int)$flexformValue > 0)) {
                    $this->settings[$flexformKey] = $flexformValue;
                }
            }
       }
    }

    /**
     * action list
     *
     * @return void
     */
    public function listAction() {

        $events = $this->eventRepository->findBySettings($this->settings);

        $this->view->assign('events', $events);
    }
}
