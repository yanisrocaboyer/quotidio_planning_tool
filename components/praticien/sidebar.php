<div class="side-bar">
    <a href="/praticien/account">
    <div class="m-1 p-2 flex">
        <div>
            <img src="/assets/unknown.jpg" alt="Unknown User" class="rounded-md" width="40px" draggable="false">
        </div>
        <div class="ml-3 leading-none flex flex-col justify-center">
            <p class="text-ml"><?php echo $praticien_user["name"] ?> <?php echo $praticien_user["surname"] ?></p>
            <p class="text-gray-500 text-xs"><?php echo $user["identifiant"] ?></p>
        </div>
    </div>
    </a>
    <hr class="text-gray-200">
    <div class="m-1 p-2 text-xl">
        <p>Ateliers</p>
        <hr class="text-gray-300">
        <div>
            <a href="/praticien/index#futur" class="flex items-center mt-2 text-primary hover:text-secondary">
                <span class="material-symbols-outlined">event_upcoming</span>
                <p class="ml-1">Ateliers à venir</p>
            </a>
        </div>
        <div>
            <a href="/praticien/index#pass" class="flex items-center mt-2 text-primary hover:text-secondary">
                <span class="material-symbols-outlined">calendar_check</span>
                <p class="ml-1">Ateliers passés</p>
            </a>
        </div>
        <div>
            <a href="/praticien/index#candidatures" class="flex items-center mt-2 text-primary hover:text-secondary">
                <span class="material-symbols-outlined">checkbook</span>
                <p class="ml-1">Participations</p>
            </a>
        </div>
    </div>
    <div class="text-xl mt-auto pt-3 bg-secondary rounded-t-xl">
        <div class="ml-5 mb-5">
        <div>
            <a href="/help/updates?type=praticien" class="flex items-center mt-2 nouveautes">
                <span class="material-symbols-outlined">new_releases</span>
                <p class="ml-1">Nouveautés</p>
            </a>
        </div>
        <div>
            <a href="/praticien/support" class="flex items-center mt-2 support">
                <span class="material-symbols-outlined">support</span>
                <p class="ml-1">Support</p>
            </a>
        </div>
        <?php
        if (!isset($_SESSION["adminTok"])) {
            ?>
            <div>
                <a href="../logout" class="flex items-center mt-1 logout">
                    <span class="material-symbols-outlined">logout</span>
                    <p class="ml-1">Déconnexion</p>
                </a>
            </div>
            </div>
            <?php
        } else {
            ?>
            <div>
                <a href="../logout?type=admin" class="flex items-center mt-1 logout">
                    <span class="material-symbols-outlined">logout</span>
                    <p class="ml-1">Repasser en admin</p>
                </a>
            </div>
            </div>
            <?php
        }
        ?>
    </div>
</div>