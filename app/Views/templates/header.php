<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Perfect Smile</title>
    <!-- Custom fonts for this template-->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <!-- Tailwind CSS -->
    <link rel="stylesheet" href="<?= base_url('css/style.css'); ?>">
    <!-- Admin Panel Styles -->
    <link rel="stylesheet" href="<?= base_url('css/admin.css'); ?>">
    <!-- Custom Styles -->
    <link rel="stylesheet" href="<?= base_url('css/custom.css'); ?>">
    <style>
        body, html {
            margin: 0 !important;
            padding: 0 !important;
            background: #ffffff !important;
            color: #1e293b !important;
        }
    </style>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <?= view('templates/alert_helper') ?>
    <?= view('templates/prompt_helper') ?>
</head>
<body style="background: #ffffff; color: #1e293b;"> 
<?php
// Admin header quick-switcher: renders a compact branch select and JS to post to admin/switch-branch
// Shows only for admin users. Uses session('selected_branch_id') as current selection.
$sessionUser = session('user') ?? null;
if ($sessionUser && ($sessionUser['user_type'] ?? '') === 'admin'):
    $branchModel = new \App\Models\BranchModel();
    $branches = $branchModel->getActiveBranches();
    $selectedBranch = session('selected_branch_id') ?? '';
?>
<style>
    /* compact header switcher */
    #adminHeaderBranchSwitcher { position: relative; display:inline-block; margin-right:1rem; }
    #adminHeaderBranchSwitcher select { min-width:160px; padding:6px 10px; border-radius:8px; border:1px solid #e5e7eb; background:#fff; font-size:0.95rem; }
    @media (max-width: 640px) { #adminHeaderBranchSwitcher select { min-width:120px; font-size:0.9rem; } }
</style>

<div id="adminHeaderBranchSwitcher" data-selected="<?= esc($selectedBranch) ?>" aria-hidden="false" style="display:none;">
    <select id="headerBranchSelect" aria-label="Select branch">
        <option value="">-- All Branches --</option>
        <?php foreach ($branches as $b): $is = ($selectedBranch == $b['id']) ? 'selected' : ''; ?>
            <option value="<?= $b['id'] ?>" <?= $is ?>><?= esc($b['name']) ?></option>
        <?php endforeach; ?>
    </select>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
    try{
        var container = document.querySelector('nav .flex.items-center.ml-auto');
        var switcher = document.getElementById('adminHeaderBranchSwitcher');
        if(!switcher) return;
        // make visible and move into topbar if available
        switcher.style.display = 'inline-block';
        if(container){
            // insert before the user avatar area
            container.insertBefore(switcher, container.firstChild);
        } else {
            // fallback: place at top of body
            document.body.insertBefore(switcher, document.body.firstChild);
        }

        var select = document.getElementById('headerBranchSelect');
        if(!select) return;
        select.addEventListener('change', function(){
            var val = select.value || '';
            var fd = new FormData();
            fd.append('branch_id', val);
            // CSRF token fallback: look for meta or template-provided hidden inputs
            var tokenMeta = document.querySelector('meta[name="csrf-token"]');
            if(tokenMeta){ fd.append(tokenMeta.getAttribute('name') || 'csrf_test_name', tokenMeta.getAttribute('content')); }
            // send POST to existing endpoint
            fetch('<?= base_url('admin/switch-branch') ?>', { method: 'POST', body: fd, credentials: 'same-origin' })
            .then(function(resp){ if(resp.ok) return resp.text(); throw resp; })
            .then(function(){ location.reload(); })
            .catch(function(err){ console.error('Branch switch failed', err); if (typeof showInvoiceAlert === 'function') showInvoiceAlert('Failed to switch branch', 'error', 4000); else alert('Failed to switch branch'); });
        });

    }catch(e){ console.error('adminHeaderBranchSwitcher init error', e); }
});
</script>
<?php endif; ?>