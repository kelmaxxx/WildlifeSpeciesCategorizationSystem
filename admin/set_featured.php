<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/lib/activity.php';
require_once __DIR__ . '/lib/settings.php';

csrf_check();

$action = $_POST['action'] ?? '';
$id     = Mongo::oid($_POST['id'] ?? null);

if ($action === 'set' && $id) {
    $species = $db->findById('species', $id);
    if ($species && ($species->approval_status ?? '') === 'approved') {
        set_setting($db, 'featured_species_id', $id);
        log_activity($db, 'update', 'species', 'featured: ' . ($species->name ?? ''));
    }
} elseif ($action === 'clear') {
    clear_setting($db, 'featured_species_id');
    log_activity($db, 'update', 'species', 'featured: cleared');
}

$back = $_POST['back'] ?? 'manage_species.php';
header('Location: ' . $back);
exit;
