define(['jquery'], function($) {
    'use strict';

    var TimeSlotWizard = {};

    TimeSlotWizard.initializeToggleButton = function() {
        var togglerSelector = '.t3js-formengine-timeslotwizard-toggle',
            toggleableSelector = '.t3js-formengine-timeslotwizard-toggleable',
            datePickersSelector = '.t3js-formengine-timeslotwizard-wrapper .t3js-datetimepicker',
            validationData = 'formengine-validation-rules';

        var $toggleable = $(toggleableSelector),
            $datePickers = $(datePickersSelector);
        $(document).on('click', togglerSelector, function(e) {
            e.preventDefault();
            $toggleable.toggleClass('hidden');

            var validationRules = $datePickers.data(validationData);
            if (validationRules[1]) {
                validationRules.pop();
            } else {
                validationRules[1] = {type: 'required'};
            }
            $datePickers.data(validationData, validationRules);
            TYPO3.FormEngine.Validation.initialize();
        });
    };

    TimeSlotWizard.initialize = function() {
        TimeSlotWizard.initializeToggleButton();
    };

    $(TimeSlotWizard.initialize);

    return TimeSlotWizard;
});
