<?php
namespace OliverKlee\Seminars\SchedulerTasks;

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
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Lang\LanguageService;
use TYPO3\CMS\Scheduler\AdditionalFieldProviderInterface;
use TYPO3\CMS\Scheduler\Controller\SchedulerModuleController;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

/**
 * This is the configuration for the e-mail notifier task.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class MailNotifierConfiguration implements AdditionalFieldProviderInterface
{
    /**
     * Gets additional fields to render in the form to add/edit a task
     *
     * @param string[] $taskInfo Values of the fields from the add/edit task form
     * @param AbstractTask|null $task The task object being edited. Null when adding a task!
     * @param SchedulerModuleController $schedulerModule Reference to the scheduler backend module
     *
     * @return string[][] a two-dimensional array
     *          array('Identifier' => array('fieldId' => array('code' => '', 'label' => '', 'cshKey' => '', 'cshLabel' => ''))
     */
    public function getAdditionalFields(array &$taskInfo, $task, SchedulerModuleController $schedulerModule)
    {
        if ($task !== null && $task instanceof MailNotifier) {
            $pageUid = $task->getConfigurationPageUid();
        } else {
            $pageUid = '';
        }
        $taskInfo['seminars_configurationPageUid'] = $pageUid;

        $fieldId = 'task-page-uid';
        $fieldCode = '<input type="text" name="tx_scheduler[seminars_configurationPageUid]" id="'
            . $fieldId . '" value="' . $pageUid . '" size="4" />';

        $additionalFields = [
            $fieldId => [
                'code' => $fieldCode,
                'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang.xlf:schedulerTasks.fields.page-uid',
                'cshKey' => '',
                'cshLabel' => '',
            ],
        ];

        return $additionalFields;
    }

    /**
     * Validates the additional field values.
     *
     * @param string[] $submittedData an array containing the data submitted by the add/edit task form
     * @param SchedulerModuleController $schedulerModule reference to the scheduler backend module
     *
     * @return bool true if validation was OK (or selected class is not relevant), false otherwise
     */
    public function validateAdditionalFields(array &$submittedData, SchedulerModuleController $schedulerModule)
    {
        $submittedData['seminars_configurationPageUid'] = (int)$submittedData['seminars_configurationPageUid'];
        $pageUid = $submittedData['seminars_configurationPageUid'];
        $hasPageUid = $pageUid > 0 && \Tx_Oelib_Db::existsRecordWithUid('pages', $pageUid);
        if ($hasPageUid) {
            return true;
        }

        $schedulerModule->addMessage(
            $this->getLanguageService()->sL(
                'LLL:EXT:seminars/Resources/Private/Language/locallang.xlf:schedulerTasks.errors.page-uid'
            ),
            FlashMessage::ERROR
        );

        return false;
    }

    /**
     * Returns $GLOBALS['LANG'].
     *
     * @return LanguageService|null
     */
    protected function getLanguageService()
    {
        return isset($GLOBALS['LANG']) ? $GLOBALS['LANG'] : null;
    }

    /**
     * Takes care of saving the additional fields' values in the task.
     *
     * @param string[] $submittedData an array containing the data submitted by the add/edit task form
     * @param AbstractTask $task the task that is being configured
     *
     * @return void
     */
    public function saveAdditionalFields(array $submittedData, AbstractTask $task)
    {
        $pageUid = !empty($submittedData['seminars_configurationPageUid'])
            ? (int)$submittedData['seminars_configurationPageUid'] : 0;

        /** @var MailNotifier$task */
        $task->setConfigurationPageUid($pageUid);
    }
}
