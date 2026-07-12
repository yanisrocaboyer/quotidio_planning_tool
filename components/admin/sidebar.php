<div class="side-bar">
    <div class="m-1 p-2 flex">
        <div>
            <img src="/assets/unknown.jpg" alt="Unknown User" class="rounded-md" width="40px" draggable="false">
        </div>
        <div class="ml-3 leading-none flex flex-col justify-center">
            <p class="text-ml">Admin</p>
            <p class="text-gray-500 text-xs"><?php echo $user["identifiant"] ?></p>
        </div>
    </div>
    <hr class="text-gray-200">
    <div class="m-1 p-2 text-xl">
        <p>Ateliers</p>
        <hr class="text-gray-300">
        <div>
            <a href=".#progress" class="flex items-center mt-2 text-primary hover:text-secondary">
                <span class="material-symbols-outlined">calendar_today</span>
                <p class="ml-1">Ateliers en cours</p>
            </a>
        </div>
        <div>
            <a href=".#future" class="flex items-center mt-2 text-primary hover:text-secondary">
                <span class="material-symbols-outlined">event_upcoming</span>
                <p class="ml-1">Ateliers à venir</p>
            </a>
        </div>
        <div>
            <a href=".#pass" class="flex items-center mt-2 text-primary hover:text-secondary">
                <span class="material-symbols-outlined">calendar_check</span>
                <p class="ml-1">Ateliers passés</p>
            </a>
        </div>
        <p class="mt-7">Gestions</p>
        <hr class="text-gray-300">
        <div>
            <a href=".#partenaire" class="flex items-center mt-2 text-primary hover:text-secondary">
                <span class="material-symbols-outlined">account_circle</span>
                <p class="ml-1">Gestion des partenaires</p>
            </a>
        </div>
        <div>
            <a href=".#praticien" class="flex items-center mt-2 text-primary hover:text-secondary">
                <span class="material-symbols-outlined">identity_platform</span>
                <p class="ml-1">Gestion des praticiens</p>
            </a>
        </div>
    </div>
    <div class="text-xl mt-auto pt-3 bg-secondary rounded-t-xl">
        <div class="ml-5 mb-5">
            <div>
                <a href=".#support" class="flex items-center mt-2 text-white hover:text-green-400">
                    <span class="material-symbols-outlined">support</span>
                    <p class="ml-1">Support</p>
                </a>
            </div>
            <div>
                <a href="../logout.php" class="flex items-center mt-1 text-white hover:text-red-300">
                    <span class="material-symbols-outlined">logout</span>
                    <p class="ml-1">Déconnexion</p>
                </a>
            </div>
        </div>
    </div>
</div>