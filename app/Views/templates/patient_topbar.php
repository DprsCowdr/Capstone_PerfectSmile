<?php $user = $user ?? session('user') ?? []; ?>
<?php
    $displayName = trim($user['name'] ?? $user['full_name'] ?? $user['username'] ?? (($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')));
    if (!$displayName) $displayName = 'Patient';
    $secondary = $user['email'] ?? ($user['id'] ?? '');
?>

<nav class="flex items-center justify-end bg-white shadow px-4 py-3 mb-6 flex-shrink-0">
    <div class="relative">
    <button id="patientAvatarBtn" aria-haspopup="true" aria-expanded="false" class="w-10 h-10 rounded-full bg-gray-200 overflow-hidden flex items-center justify-center focus:outline-none pulse-affordance">
            <?php if (!empty($user['avatar'])): ?>
                <img src="<?= esc($user['avatar']) ?>" alt="<?= esc($displayName) ?>" class="w-full h-full object-cover">
            <?php else: ?>
                <?php
                    $initials = '';
                    $parts = preg_split('/\s+/', $displayName);
                    foreach ($parts as $p) { if ($p) $initials .= mb_substr($p, 0, 1); if (mb_strlen($initials) >= 2) break; }
                    $initials = strtoupper($initials ?: mb_substr($displayName, 0, 1));
                ?>
                <span class="text-sm font-medium text-gray-700"><?= esc($initials) ?></span>
            <?php endif; ?>
        </button>

        <div id="patientDropdown" class="hidden absolute right-0 mt-2 w-64 bg-white rounded-md shadow-lg z-50 ring-1 ring-black ring-opacity-5">
            <div class="p-4 border-b">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-full bg-gray-100 overflow-hidden flex items-center justify-center mr-3">
                        <?php if (!empty($user['avatar'])): ?>
                            <img src="<?= esc($user['avatar']) ?>" alt="<?= esc($displayName) ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <span class="text-sm font-medium text-gray-700"><?= esc($initials) ?></span>
                        <?php endif; ?>
                    </div>
                    <div>
                        <div class="text-sm font-semibold text-gray-800"><?= esc($displayName) ?></div>
                        <div class="text-xs text-gray-500 truncate" title="<?= esc($secondary) ?>"><?= esc($secondary) ?></div>
                    </div>
                </div>
            </div>
            <div class="p-2">
                <div class="grid grid-cols-1 gap-2">
                    <a href="<?= base_url('patient/profile') ?>" class="block px-3 py-2 rounded hover:bg-gray-100">👤 Profile Information</a>
                    <a href="<?= base_url('patient/security') ?>" class="block px-3 py-2 rounded hover:bg-gray-100">🔑 Account & Security</a>
                    <a href="<?= base_url('patient/preferences') ?>" class="block px-3 py-2 rounded hover:bg-gray-100">⚙️ Preferences</a>
                    <a href="<?= base_url('patient/privacy') ?>" class="block px-3 py-2 rounded hover:bg-gray-100">🔒 Privacy</a>
                    <a href="<?= base_url('patient/support') ?>" class="block px-3 py-2 rounded hover:bg-gray-100">🆘 Support</a>
                </div>
                <div class="border-t my-2"></div>
                <a href="<?= base_url('auth/logout') ?>" class="block px-3 py-2 rounded text-red-600 hover:bg-gray-100">🚪 Logout</a>
            </div>
        </div>
    </div>
</nav>

<script>
(() => {
    const btn = document.getElementById('patientAvatarBtn');
    const dropdown = document.getElementById('patientDropdown');

    if (!btn || !dropdown) return;

    const openDropdown = () => {
        if (window.innerWidth <= 640) {
            // mobile: full-screen slide panel
            dropdown.classList.remove('absolute', 'right-0', 'mt-2', 'w-64');
            dropdown.classList.add('fixed', 'inset-0', 'w-full', 'h-full', 'overflow-auto');
            dropdown.style.background = 'white';
        } else {
            dropdown.classList.remove('fixed', 'inset-0', 'w-full', 'h-full', 'overflow-auto');
            dropdown.classList.add('absolute', 'right-0', 'mt-2', 'w-64');
            dropdown.style.background = '';
        }
        dropdown.classList.remove('hidden');
        btn.setAttribute('aria-expanded', 'true');
        document.addEventListener('click', outsideClick);
    };

    const closeDropdown = () => {
        dropdown.classList.add('hidden');
        btn.setAttribute('aria-expanded', 'false');
        document.removeEventListener('click', outsideClick);
    };

    const outsideClick = (e) => {
        if (!dropdown.contains(e.target) && !btn.contains(e.target)) closeDropdown();
    };

    btn.addEventListener('click', (e) => {
        e.stopPropagation();
        if (dropdown.classList.contains('hidden')) {
            openDropdown();
            // remove pulse once user has interacted
            btn.classList.remove('pulse-affordance');
        } else {
            closeDropdown();
        }
    });
})();
</script>

<style>
/* subtle pulse to draw attention to avatar on first load */
.pulse-affordance {
    position: relative;
}
.pulse-affordance::after {
    content: '';
    position: absolute;
    inset: -4px;
    border-radius: 9999px;
    background: rgba(59,130,246,0.12); /* blue-500 @ 12% */
    animation: pulseOnce 1.6s ease-in-out;
}
@keyframes pulseOnce {
    0% { transform: scale(0.9); opacity: 0; }
    10% { opacity: 1; }
    70% { transform: scale(1.15); opacity: 0.65; }
    100% { transform: scale(1.35); opacity: 0; }
}
@media (prefers-reduced-motion: reduce) {
    .pulse-affordance::after { animation: none; }
}
</style>
