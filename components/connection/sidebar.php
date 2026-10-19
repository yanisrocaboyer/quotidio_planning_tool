<?php
$sidebar = isset($_GET['sidebar']) ? $_GET['sidebar'] : 0;
?>

<div class="side-bar">
    <div class="m-1 p-2 flex justify-center items-center">
        <img src="/assets/quotidio_big_logo.png" width="200px">
    </div>
    <div class="m-1 p-2 text-xl">
        <p>Connection</p>
        <hr class="text-gray-300">
        <div>
            <a href="/connection/praticien?sidebar=<?= $sidebar ?>" class="flex items-center mt-2 text-primary hover:text-secondary">
                <span class="material-symbols-outlined">badge</span>
                <p class="ml-1">Praticien</p>
            </a>
        </div>
        <div>
            <a href="/connection/partenaire?sidebar=<?= $sidebar ?>" class="flex items-center mt-2 text-primary hover:text-secondary">
                <span class="material-symbols-outlined">partner_exchange</span>
                <p class="ml-1">Partenaire</p>
            </a>
        </div>
        <div>
            <a href="/connection/admin?sidebar=<?= $sidebar ?>" class="flex items-center mt-2 text-primary hover:text-secondary">
                <span class="material-symbols-outlined">identity_platform</span>
                <p class="ml-1">Administrateur</p>
            </a>
        </div>
    </div>
    <div class="text-xl mt-auto pt-3 bg-secondary rounded-t-xl">
        <div class="ml-5 mb-5">
            <div>
                <a href="/connection/support?sidebar=<?= $sidebar ?>" class="flex items-center mt-2 support">
                    <span class="material-symbols-outlined">support</span>
                    <p class="ml-1">Support</p>
                </a>
            </div>
        </div>
    </div>
</div>