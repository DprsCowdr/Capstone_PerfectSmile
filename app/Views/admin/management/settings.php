<?php
// Backwards-compatible wrapper: delegate to the new admin/settings view path.
// This file kept for compatibility; main content now lives in app/Views/admin/settings/settings.php
echo view('admin/settings/settings', ['user' => $user ?? null]);
?>