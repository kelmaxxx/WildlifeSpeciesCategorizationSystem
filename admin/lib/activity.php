<?php
/**
 * Activity log helper. Records every meaningful admin action so we can
 * surface "who did what" on the dashboard.
 */
function log_activity(Mongo $db, string $action, string $targetType, string $targetName): void
{
    $db->insert('activity_log', [
        'action'         => $action,         // create | update | delete | approve | reject
        'target_type'    => $targetType,     // species | category | habitat | user
        'target_name'    => $targetName,
        'actor_username' => $_SESSION['admin_username'] ?? 'system',
        'created_at'     => new MongoDB\BSON\UTCDateTime(),
    ]);
}

/** Map action -> short label */
function activity_verb(string $action): string {
    return [
        'create'  => 'created',
        'update'  => 'updated',
        'delete'  => 'deleted',
        'approve' => 'approved',
        'reject'  => 'rejected',
    ][$action] ?? $action;
}

/** Map action -> CSS class for the activity icon */
function activity_icon_class(string $action): string {
    return [
        'create'  => 'create',
        'update'  => 'update',
        'delete'  => 'delete',
        'approve' => 'approve',
        'reject'  => 'reject',
    ][$action] ?? 'create';
}

/** Map action -> single-letter glyph */
function activity_icon_glyph(string $action): string {
    return [
        'create'  => '+',
        'update'  => '✎',
        'delete'  => '×',
        'approve' => '✓',
        'reject'  => '!',
    ][$action] ?? '·';
}

/** Format a UTCDateTime into a relative "x ago" string */
function format_when($utcDate): string {
    if (!$utcDate instanceof MongoDB\BSON\UTCDateTime) return '';
    $when = $utcDate->toDateTime()->getTimestamp();
    $diff = time() - $when;
    if ($diff < 60)        return 'just now';
    if ($diff < 3600)      return floor($diff / 60) . ' min ago';
    if ($diff < 86400)     return floor($diff / 3600) . ' h ago';
    if ($diff < 7 * 86400) return floor($diff / 86400) . ' d ago';
    return date('M j', $when);
}
