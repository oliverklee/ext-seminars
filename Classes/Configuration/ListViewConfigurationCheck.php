<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Configuration;

/**
 * Configuration check for the list view.
 *
 * @internal
 */
class ListViewConfigurationCheck extends AbstractFrontEndConfigurationCheck
{
    protected function checkAllConfigurationValues(): void
    {
        $this->checkCommonFrontEndSettings();

        $this->checkRegistrationFlag();

        $this->checkPages();
        $this->checkRecursive();

        // This is checked for the list view as well because an invalid value
        // might cause the list view to be displayed instead of the single view.
        $this->checkShowSingleEvent();
        $this->checkHideColumns();
        $this->checkTimeframeInList();
        $this->checkShowEmptyEntryInOptionLists();
        $this->checkHidePageBrowser();
        $this->checkHideCanceledEvents();
        $this->checkSortListViewByCategory();
        $this->checkGeneralPriceInList();
        $this->checkListPid();
        $this->checkDetailPid();
        if ($this->isRegistrationEnabled()) {
            $this->checkRegisterPid();
            $this->checkLoginPid();
        }
        $this->checkAccessToFrontEndRegistrationLists();
        $this->checkRegistrationsListPidOptional();
        $this->checkRegistrationsVipListPidOptional();
        $this->checkLimitListViewToEventTypes();
        $this->checkLimitListViewToCategories();
        $this->checkLimitListViewToPlaces();
        $this->checkLimitListViewToOrganizers();
        $this->checkSeminarImageSizesForListView();
        $this->checkDisplaySearchFormFields();
        $this->checkNumberOfYearsInDateFilter();
        $this->checkLimitFileDownloadToAttendees();
        $this->checkShowOnlyEventsWithVacancies();
        $this->checkEnableSortingLinksInListView();
        $this->checkLinkToSingleView();
    }

    private function checkHideColumns(): void
    {
        $this->checkIfMultiInSetOrEmpty(
            'hideColumns',
            'This value specifies which columns to remove from the list view.
            Incorrect values will cause the columns to still be displayed.',
            [
                'image',
                'category',
                'title',
                'subtitle',
                'uid',
                'event_type',
                'accreditation_number',
                'credit_points',
                'teaser',
                'speakers',
                'date',
                'time',
                'expiry',
                'place',
                'city',
                'seats',
                'price_regular',
                'price_special',
                'total_price',
                'organizers',
                'target_groups',
                'attached_files',
                'vacancies',
                'status_registration',
                'registration',
                'list_registrations',
            ],
        );
    }

    private function checkShowEmptyEntryInOptionLists(): void
    {
        $this->checkIfBoolean(
            'showEmptyEntryInOptionLists',
            'This value specifies whether the option boxes in the selector widget will contain
            a dummy entry called &quot;not selected&quot;.
            This is only needed if you changed the HTML template to show the selectors as drop-down menus.
            If this value is incorrect, the dummy entry might get displayed when this is not intended (or vice versa).',
        );
    }

    private function checkHidePageBrowser(): void
    {
        $this->checkIfBoolean(
            'hidePageBrowser',
            'This value specifies whether the page browser in the list view will be displayed.
            If this value is incorrect, the page browser might get displayed when this is not intended (or vice versa).',
        );
    }

    private function checkHideCanceledEvents(): void
    {
        $this->checkIfBoolean(
            'hideCanceledEvents',
            'This value specifies whether canceled events will be removed from the list view.
            If this value is incorrect, canceled events might get displayed when this is not intended (or vice versa).',
        );
    }

    private function checkSortListViewByCategory(): void
    {
        $this->checkIfBoolean(
            'sortListViewByCategory',
            'This value specifies whether the list view should be sorted by category before applying the normal sorting.
            If this value is incorrect, the list view might get sorted by category when this is not intended
            (or vice versa).',
        );
    }

    private function checkGeneralPriceInList(): void
    {
        $this->checkIfBoolean(
            'generalPriceInList',
            'This value specifies whether the column header for the standard
            price in the list view will be just <em>Price</em> instead
            of <em>Standard price</em>.
            If this value is incorrect, the wrong label might be used.',
        );
    }

    private function checkAccessToFrontEndRegistrationLists(): void
    {
        $this->checkIfSingleInSetNotEmpty(
            'accessToFrontEndRegistrationLists',
            'This value specifies who is able to see the registered persons an event in the front end .
            If this value is incorrect, persons may access the registration lists although they should not be allowed to
            (or vice versa).',
            ['attendees_and_managers', 'login'],
        );
    }

    private function checkRegistrationsListPidOptional(): void
    {
        $this->checkIfNonNegativeIntegerOrEmpty(
            'registrationsListPID',
            'This value specifies the page that contains the list of registrations for an event.
            If this value is not set correctly, the link to that page will not work.',
        );
    }

    private function checkRegistrationsVipListPidOptional(): void
    {
        $this->checkIfNonNegativeIntegerOrEmpty(
            'registrationsVipListPID',
            'This value specifies the page that contains the list of registrations for an event.
            If this value is not set correctly, the link to that page will not work.',
        );
    }

    private function checkLimitListViewToEventTypes(): void
    {
        $this->checkIfIntegerListOrEmpty(
            'limitListViewToEventTypes',
            'This value specifies the event types by which the list view should be filtered.
            If this value is not set correctly, some events might unintentionally get hidden or shown.',
        );
    }

    private function checkLimitListViewToCategories(): void
    {
        $this->checkIfIntegerListOrEmpty(
            'limitListViewToCategories',
            'This value specifies the categories by which the list view should be filtered.
            If this value is not set correctly, some events might unintentionally get hidden or shown.',
        );
    }

    private function checkLimitListViewToPlaces(): void
    {
        $this->checkIfIntegerListOrEmpty(
            'limitListViewToPlaces',
            'This value specifies the places for which the list view should be filtered.
            If this value is not set correctly, some events might unintentionally get hidden or shown.',
        );
    }

    private function checkLimitListViewToOrganizers(): void
    {
        $this->checkIfIntegerListOrEmpty(
            'limitListViewToOrganizers',
            'This value specifies the organizers for which the list view should be filtered.
            If this value is not set correctly, some events might unintentionally get hidden or shown.',
        );
    }

    /**
     * Checks the settings for the image width and height in the list view.
     */
    private function checkSeminarImageSizesForListView(): void
    {
        $this->checkListViewImageWidth();
        $this->checkListViewImageHeight();
    }

    private function checkListViewImageWidth(): void
    {
        $this->checkIfPositiveInteger(
            'seminarImageListViewWidth',
            'This value specifies the width of the image of a seminar.
            If this value is not set, the image will be shown in full size.',
        );
    }

    private function checkListViewImageHeight(): void
    {
        $this->checkIfPositiveInteger(
            'seminarImageListViewHeight',
            'This value specifies the height of the image of a seminar.
            If this value is not set, the image will be shown in full size.',
        );
    }

    private function checkDisplaySearchFormFields(): void
    {
        $this->checkIfMultiInSetOrEmpty(
            'displaySearchFormFields',
            'This value specifies which search widget fields to display in the list view.
            The search widget will not display any fields at all if this value is empty or contains only invalid keys.',
            [
                'event_type',
                'city',
                'place',
                'full_text_search',
                'date',
                'age',
                'organizer',
                'categories',
                'price',
            ],
        );
    }

    private function checkNumberOfYearsInDateFilter(): void
    {
        $this->checkIfPositiveInteger(
            'numberOfYearsInDateFilter',
            'This value specifies the number years of years the user can search for events in the event list.
            The date search will have an empty drop-down for the year if this variable is misconfigured.',
        );
    }

    private function checkEnableSortingLinksInListView(): void
    {
        $this->checkIfBoolean(
            'enableSortingLinksInListView',
            'This value specifies whether the list view header should be sorting links.
            If this value is incorrect, the sorting might be enabled even when this is not desired (or vice versa).',
        );
    }

    private function checkLinkToSingleView(): void
    {
        $this->checkIfSingleInSetNotEmpty(
            'linkToSingleView',
            'This value specifies when the list view will link to the single view.
            If this value is not set correctly, the single view might not be linked although this is intended
            (or vice versa).',
            ['always', 'never', 'onlyForNonEmptyDescription'],
        );
    }
}
