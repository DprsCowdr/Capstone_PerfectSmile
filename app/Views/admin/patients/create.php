<?= view('templates/header') ?>
<div id="wrapper">
    <?= view('templates/sidebar', ['user' => $user]) ?>
    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <div class="container mt-5">
                <h1 class="fw-bold mb-4">Create Account for <?= esc($patient['name']) ?></h1>
                <p>Email: <strong><?= esc($patient['email']) ?></strong></p>
                <form action="/admin/patients/save-account/<?= $patient['id'] ?>" method="post">
                    <div class="mb-3">
                        <label for="password" class="form-label">Set Password</label>
                        <input type="password" class="form-control" id="password" name="password" required minlength="6">
                        <div class="form-text">Password must be at least 6 characters.</div>
                    </div>
                    <button type="submit" class="btn btn-primary">Create Account</button>
                    <a href="/admin/patients" class="btn btn-secondary ms-2">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
<?= view('templates/footer') ?> 