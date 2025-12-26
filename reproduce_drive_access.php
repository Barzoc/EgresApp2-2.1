<?php
require_once __DIR__ . '/lib/GoogleDriveClient.php';

$folderId = '1sTE4iJ9ZzGOYNhzvrxGPVGKP7jqQw_dJ';

echo "Testing access to folder: $folderId\n";

try {
    $client = new GoogleDriveClient();
    if (!$client->isEnabled()) {
        die("Google Drive client is not enabled.\n");
    }

    $files = $client->listFolderFiles($folderId);
    echo "Successfully listed " . count($files) . " files.\n";
    foreach (array_slice($files, 0, 5) as $file) {
        echo "- " . $file['name'] . " (" . $file['id'] . ")\n";
    }

} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
