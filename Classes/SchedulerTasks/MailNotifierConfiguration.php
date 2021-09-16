<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\SchedulerTasks;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Lang\LanguageService;
use TYPO3\CMS\Scheduler\AdditionalFieldProviderInterface;
use TYPO3\CMS\Scheduler\Controller\SchedulerModuleController;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

/**
 * This is the configuration for the e-mail notifier task.
 */
class MailNotifierConfiguration implements AdditionalFieldProviderInterface
{
    /**
     * @var string
     */
    const LABEL_PREFIX = 'LLL:EXT:seminars/Resources/Private/Language/locallang.xlf:';

    /**
     * Gets additional fields to render in the form to add/edit a task
     *
     * @param string[] $taskInfo Values of the fields from the add/edit task form
     * @param AbstractTask|null $task The task object being edited. Null when adding a task!
     * @param SchedulerModuleController $schedulerModule Reference to the scheduler backend module
     *
     * @return string[][] a two-dimensional array
     *          array('Identifier' => array('fieldId' => array('code' => '', 'label' => ''))
     */
    public function getAdditionalFields(array &$taskInfo, $task, SchedulerModuleController $schedulerModule): array
    {
        $pageUid = $task instanceof MailNotifier ? (string)$task->getConfigurationPageUid() : '';
        $taskInfo['seminars_configurationPageUid'] = $pageUid;

        $fieldId = 'task-page-uid';
        $fieldCode = '<input type="text" name="tx_scheduler[seminars_configurationPageUid]" id="'
            . $fieldId . '" value="' . $pageUid . '" size="4" />';

        return [
            $fieldId => [
                'code' => $fieldCode,
                'label' => self::LABEL_PREFIX . 'schedulerTasks.fields.page-uid',
            ],
        ];
    }

    /**
     * Validates the additional field values.
     *
     * @param string[] $submittedData an array containing the data submitted by the add/edit task form
     * @param SchedulerModuleController $schedulerModule reference to the scheduler backend module
     *
     * @return bool true if validation was OK (or selected class is not relevant), false otherwise
     */
    public function validateAdditionalFields(array &$submittedData, SchedulerModuleController $schedulerModule): bool
    {
        $pageUid = (int)$submittedData['seminars_configurationPageUid'];
        $submittedData['seminars_configurationPageUid'] = $pageUid;

        /** @var \TYPO3\CMS\Core\Database\Connection $connection */
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('pages');
        $pageWithUidExist = $connection->count('*', 'pages', ['uid' => $pageUid]) > 0;
        $hasPageUid = $pageUid > 0 && $pageWithUidExist;
        if ($hasPageUid) {
            return true;
        }

        $message = $this->getLanguageService()->sL(self::LABEL_PREFIX . 'schedulerTasks.errors.page-uid');
        $this->addMessage($message, FlashMessage::ERROR);

        return false;
    }

    /**
     * Adds a flash message.
     *
     * Once TYPO3 >= 9.5 is required, this class can extend `AbstractAdditionalFieldProvider`, and this method
     * can be removed.
     *
     * @param string $message the flash message content
     * @param int $severity the flash message severity
     */
    private function addMessage(string $message, int $severity = FlashMessage::OK)
    {
        /** @var FlashMessage $flashMessage */
        $flashMessage = GeneralUtility::makeInstance(FlashMessage::class, $message, '', $severity);
        /** @var FlashMessageService $service */
        $service = GeneralUtility::makeInstance(FlashMessageService::class);
        $queue = $service->getMessageQueueByIdentifier();
        $queue->enqueue($flashMessage);
    }

    /**
     * @return LanguageService
     */
    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
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
        $pageUid = (int)($submittedData['seminars_configurationPageUid'] ?? 0);

        /** @var MailNotifier $task */
        $task->setConfigurationPageUid($pageUid);
    }
}
