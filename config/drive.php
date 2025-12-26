<?php
return [
    'enabled' => (bool) getenv('DRIVE_BACKUP_ENABLED') ?: true,
    'folder_id' => getenv('DRIVE_FOLDER_ID') ?: '1sTE4iJ9ZzGOYNhzvrxGPVGKP7jqQw_dJ',
    'service_account_file' => __DIR__ . '/hip-orbit-458817-b4-2b774255881e.json',
];
