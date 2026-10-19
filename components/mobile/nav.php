<?php

$mobile_role = null;
$mobile_first = isset($url) ? explode('/', $url)[0] : '';
if (in_array($mobile_first, array('admin', 'praticien', 'partenaire'), true)) {
    $mobile_role = $mobile_first;
} elseif ($mobile_first === 'help' && isset($user['type'])) {
    $mobile_type_to_role = array('admin' => 'admin', 'praticien' => 'praticien', 'client' => 'partenaire');
    if (isset($mobile_type_to_role[$user['type']])) {
        $mobile_role = $mobile_type_to_role[$user['type']];
    }
}

if ($mobile_role === null) {
    return;
}

$mobile_expected_type = ($mobile_role === 'partenaire') ? 'client' : $mobile_role;
if (empty($user) || ($user['type'] ?? null) !== $mobile_expected_type) {
    return;
}

$mobile_display_name = '';
$mobile_avatar_href = '/' . $mobile_role . '/index';

if ($mobile_role === 'admin') {
    $mobile_display_name = $user['identifiant'] ?? '';
} elseif ($mobile_role === 'praticien') {
    if (isset($praticien_user['name'])) {
        $mobile_display_name = trim(($praticien_user['name'] ?? '') . ' ' . ($praticien_user['surname'] ?? ''));
    } else {
        $mobile_display_name = $user['identifiant'] ?? '';
    }
    $mobile_avatar_href = '/praticien/account';
} else {
    $mobile_display_name = $client_user['name'] ?? ($user['identifiant'] ?? '');
}

$mobile_initial_source = trim($mobile_display_name) !== '' ? trim($mobile_display_name) : $mobile_role;
$mobile_initial = function_exists('mb_substr')
    ? mb_strtoupper(mb_substr($mobile_initial_source, 0, 1))
    : strtoupper(substr($mobile_initial_source, 0, 1));

$mobile_updates_href = '/help/updates?type=' . urlencode($mobile_role);
$mobile_on_updates = ($mobile_first === 'help');

$mobile_tabs = array();
if ($mobile_role === 'admin') {
    $mobile_tabs = array(
        array('Ateliers', '/admin/index', 'calendar_today'),
        array('Partenaires', '/admin/client_gestion', 'partner_exchange'),
        array('Praticiens', '/admin/praticien_gestion', 'badge'),
        array('Types', '/admin/type_gestion', 'category'),
    );
} elseif ($mobile_role === 'praticien') {
    $mobile_tabs = array(
        array('Ateliers', '/praticien/index', 'calendar_today'),
        array('Candidatures', '/praticien/index#candidatures', 'checkbook'),
        array('Compte', '/praticien/account', 'account_circle'),
    );
} else {
    $mobile_tabs = array(
        array('Ateliers', '/partenaire/index', 'calendar_today'),
        array('Support', '/partenaire/support', 'support_agent'),
    );
}
?>
<header class="mobile-topbar">
    <button type="button" class="mobile-topbar-btn" id="mobileMenuBtn" aria-label="Ouvrir le menu" aria-controls="mobileSidebar" aria-expanded="false">
        <span class="material-symbols-outlined">menu</span>
    </button>
    <a href="/<?= htmlspecialchars($mobile_role) ?>/index" class="mobile-brand"><img src="/assets/quotidio_big_logo.png" width="100px" /></a>
    <div class="mobile-topbar-actions">
        <a href="<?= htmlspecialchars($mobile_updates_href) ?>" class="mobile-topbar-btn" aria-label="Nouveautés" title="Nouveautés">
            <span class="material-symbols-outlined">new_releases</span>
        </a>
        <a href="/<?= $mobile_role ?>/support.php" class="mobile-topbar-btn" aria-label="Support" title="Support">
            <span class="material-symbols-outlined">support</span>
        </a>
        <a href="<?= htmlspecialchars($mobile_avatar_href) ?>" class="mobile-avatar" title="<?= htmlspecialchars(trim($mobile_display_name)) ?>" aria-label="Mon compte">
            <?= htmlspecialchars($mobile_initial) ?>
        </a>
    </div>
</header>

<nav class="mobile-tabbar" aria-label="Navigation principale">
    <?php foreach ($mobile_tabs as $tab):
        $tab_path = parse_url($tab[1], PHP_URL_PATH);
        $tab_hash = parse_url($tab[1], PHP_URL_FRAGMENT);
    ?>
        <a href="<?= htmlspecialchars($tab[1]) ?>"
           data-path="<?= htmlspecialchars($tab_path) ?>"
           data-hash="<?= $tab_hash ? '#'.htmlspecialchars($tab_hash) : '' ?>">
            <span class="material-symbols-outlined"><?= htmlspecialchars($tab[2]) ?></span>
            <span><?= htmlspecialchars($tab[0]) ?></span>
        </a>
    <?php endforeach; ?>
</nav>

<div class="sidebar-backdrop" id="sidebarBackdrop" aria-hidden="true"></div>

<script>
(function () {
    var body = document.body;
    var sidebar = document.querySelector('.side-bar');
    var menuBtn = document.getElementById('mobileMenuBtn');
    var backdrop = document.getElementById('sidebarBackdrop');

    if (!sidebar) return;

    sidebar.id = sidebar.id || 'mobileSidebar';

    var closeBtn = document.createElement('button');
    closeBtn.type = 'button';
    closeBtn.className = 'sidebar-close';
    closeBtn.setAttribute('aria-label', 'Fermer le menu');
    closeBtn.innerHTML = '<span class="material-symbols-outlined">close</span>';
    sidebar.insertBefore(closeBtn, sidebar.firstChild);

    function openSidebar() {
        body.classList.add('sidebar-open');
        if (menuBtn) menuBtn.setAttribute('aria-expanded', 'true');
    }
    function closeSidebar() {
        body.classList.remove('sidebar-open');
        if (menuBtn) menuBtn.setAttribute('aria-expanded', 'false');
    }
    function toggleSidebar() {
        if (body.classList.contains('sidebar-open')) closeSidebar();
        else openSidebar();
    }

    if (menuBtn) menuBtn.addEventListener('click', toggleSidebar);
    if (backdrop) backdrop.addEventListener('click', closeSidebar);
    closeBtn.addEventListener('click', closeSidebar);

    sidebar.addEventListener('click', function (e) {
        if (e.target.closest('a')) closeSidebar();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeSidebar();
    });

    document.querySelectorAll('.mobile-tabbar a[data-path]').forEach(function (a) {
        var active = a.dataset.path === window.location.pathname
            && (a.dataset.hash || '') === (window.location.hash || '');
        a.classList.toggle('active', active);
    });

    var startX = 0, startY = 0, tracking = false;
    sidebar.addEventListener('touchstart', function (e) {
        if (!body.classList.contains('sidebar-open')) return;
        startX = e.touches[0].clientX;
        startY = e.touches[0].clientY;
        tracking = true;
    }, { passive: true });
    sidebar.addEventListener('touchmove', function (e) {
        if (!tracking) return;
        var dx = e.touches[0].clientX - startX;
        var dy = e.touches[0].clientY - startY;
        if (dx < -40 && Math.abs(dx) > Math.abs(dy)) {
            tracking = false;
            closeSidebar();
        }
    }, { passive: true });
    sidebar.addEventListener('touchend', function () { tracking = false; }, { passive: true });
})();
</script>
