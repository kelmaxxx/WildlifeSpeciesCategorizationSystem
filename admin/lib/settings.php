<?php
/**
 * Settings helper — small key/value store backed by a `settings` collection.
 * Used for site-wide admin choices like the featured species on the homepage.
 */

function get_setting(Mongo $db, string $key, $default = null)
{
    $row = $db->findOne('settings', ['key' => $key]);
    return $row->value ?? $default;
}

function set_setting(Mongo $db, string $key, $value): void
{
    $existing = $db->findOne('settings', ['key' => $key]);
    if ($existing) {
        $db->update('settings', ['key' => $key], ['value' => $value]);
    } else {
        $db->insert('settings', ['key' => $key, 'value' => $value]);
    }
}

function clear_setting(Mongo $db, string $key): void
{
    $db->delete('settings', ['key' => $key]);
}
