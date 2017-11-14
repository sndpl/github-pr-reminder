<?php

return [
    /*
     * Here goes the application configuration.
     */
    'app' => [

        /*
         * Here goes the application name.
         */
        'name' => 'GitHub PR Reminder',

        /*
         * Here goes the application version.
         */
        'version' => '1.0.0',

        /*
         * If true, development commands won't be available as the app
         * will be in the production environment.
         */
        'production' => false,

        /*
         * Here goes the application default command. By default
         * the list of commands will appear. All commands
         * application commands will be auto-detected.
         *
         * 'default-command' => App\Commands\RunCommand::class,
        */
        'default-command' => App\Commands\GitHubPrReminderCommand::class,

        /*
         * Here goes the application list of Laravel Service Providers.
         * Enjoy all the power of Laravel on your console.
         */
        'providers' => [
            App\Providers\AppServiceProvider::class,
        ],
    ],
    // Slack configuration
    'slack' => [
        'bot-name' => 'bot',
        'bot-icon' => '',
        'channel'  => '#test',
        'token'    => '',
        'language-icon' => [
            'PHP' => ':php:',
            'Kotlin' => ':kotlin:',
            'Swift' => ':swift:',
            'Java' => ':android:',
        ]
    ],
    // GitHub Configuration
    'git-hub' => [
        'exclude-labels' => ['WIP', 'DO NOT MERGE'],
        'token' => '',
        'organizations' => [
            [
                'name' =>  'github-organisation-name',
                'exclude-repositories' => [],
                'include-repositories' => [],
            ],
        ]
    ]
];
