<?php

use OliverKlee\Seminars\Middleware\ResponseHeadersModifier;

return [
    'backend' => [
        'oliverklee/seminars/response-headers-modifier' => [
            'target' => ResponseHeadersModifier::class,
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
            'target' => ResponseHeadersModifier::class,
            'after' => [
                'typo3/cms-frontend/output-compression',
            ],
            'before' => [
                'typo3/cms-core/response-propagation ',
            ],
        ],
    ],
];
