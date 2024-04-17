<?php

return [
    'backend' => [
        'oliverklee/seminars/response-headers-modifier' => [
            'target' => \OliverKlee\Seminars\Middleware\ResponseHeadersModifier::class,
            'after' => [
                'typo3/cms-backend/output-compression',
            ],
            'before' => [
                'typo3/cms-backend/response-headers',
            ],
        ],
    ],
    'frontend' => [
        'oliverklee/seminars/response-headers-modifier' => [
            'target' => \OliverKlee\Seminars\Middleware\ResponseHeadersModifier::class,
            'after' => [
                'typo3/cms-frontend/output-compression',
            ],
            'before' => [
                'typo3/cms-core/response-propagation ',
            ],
        ],
    ],
];
