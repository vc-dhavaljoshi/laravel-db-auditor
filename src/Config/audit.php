<?php
// config for Vcian/LaravelDbAuditor
use Vcian\LaravelDBAuditor\Constants\Constant;

return [
    'skip_tables' => [ // Add table name that you want to skip
        'cache',
        'sqlite_sequence',
        'migrations',
        'migrations_history',
        'password_resets',
        'failed_jobs',
        'jobs',
        'queue_job',
        'queue_failed_jobs'
    ],
    'mysql_commands' => [
        Constant::STANDARD_COMMAND,
        Constant::CONSTRAINT_COMMAND,
        Constant::SUMMARY_COMMAND,
        Constant::TRACK_COMMAND,
        Constant::CHECK_PERFORMANCE_PARAMETER_COMMAND,
    ],
    'sqlite_commands' => [
        Constant::STANDARD_COMMAND,
        Constant::CONSTRAINT_COMMAND,
        Constant::SUMMARY_COMMAND,
        Constant::TRACK_COMMAND,
    ],
    'pgsql_commands' => [
        Constant::STANDARD_COMMAND,
        Constant::CONSTRAINT_COMMAND,
        Constant::SUMMARY_COMMAND,
        Constant::TRACK_COMMAND,
    ]
];
