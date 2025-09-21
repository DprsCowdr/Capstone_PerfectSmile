<?php
// Backwards-compatible wrapper: delegate to the new admin/settings partial.
// The actual template editor now lives at app/Views/admin/settings/message_templates.php
// When included from settings, that view expects a $templates variable; we fetch it here for parity.
$path = WRITEPATH . 'appointment_messages.json';
if (is_file($path)) {
    $json = file_get_contents($path);
    $templates = json_decode($json, true) ?: [];
} else {
    $cfg = config('AppointmentMessages');
    $templates = $cfg->templates;
}

echo view('admin/settings/message_templates', ['templates' => $templates]);
?>
