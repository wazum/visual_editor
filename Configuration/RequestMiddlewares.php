<?php

use Andersundsehr\Editara\Middleware\EditaraPersistenceMiddleware;

return [
    'frontend' => [
        'andersundsehr/editara/editara-persistence-middleware' => [
            'target' => EditaraPersistenceMiddleware::class,
            'before' => [
                'typo3/cms-frontend/prepare-tsfe-rendering',
            ],
            'after' => [
                'typo3/cms-frontend/tsfe',
                'typo3/cms-frontend/page-resolver',
                'typo3/cms-adminpanel/sql-logging',
            ],
        ],
    ],
];
