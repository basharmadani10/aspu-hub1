<?php

return [
    'roles_and_permissions' => [
        'Super Admin' => [
            'full_access',
            'create_users',
            'edit_users',
            'deactivate_users',
            'delete_users',
            'assign_roles',
            'modify_roles',
            'manage_question_banks',
            'manage_questions',
            'access_reports',
            'access_analytics',
            'access_system_logs',
            'perform_backups',
            'restore_backups',
            'send_notifications',
            'monitor_login_activities',
            'manage_security_settings',
            'create_custom_roles',
            'create_custom_permissions',
            'override_restrictions',
        ],
        'Admin' => [
            'manage_users',
            'assign_roles',
            'create_question_banks',
            'organize_question_banks',
            'review_questions',
            'approve_questions',
            'generate_reports',
            'perform_backups',
            'send_notifications',
            'access_security_settings',
            'access_activity_logs',
        ],
 
        'Student' => [
            'access_tests',
            'view_grades',
            'review_test_answers',
            'submit_feedback',
        ],
    ],
];