<?php
$TITLE = "Quotidio Planning - Connexion";
$DIPLOMA_LIMIT = 5;
require_once './functions/connection.php';


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $register_result = register($_POST, $_FILES);

    if ($register_result['success']) {
        header("Location: ../praticien/inscription_success.php");
        exit;
    } else if (!empty($register_result['errors'])) {
        $form_errors = $register_result['errors'];
    }

}
?>
<div class="min-h-screen flex flex-col justify-center items-center bg-[url('/assets/bg.png')] bg-no-repeat bg-bottom bg-cover py-10">
    <div class="bg-white w-full max-w-3xl shadow-lg px-10 py-8 mx-2 overflow-auto sm:rounded-xl">
        <img class="w-64 md:w-80 mx-auto" src="/assets/quotidio_big_logo.png" alt="quotidio_big_logo" draggable="false">
        <div class="mt-4">
            <h1 class="mt-2 text-3xl md:text-4xl text-center text-primary">Inscription praticien·ne</h1>
            <div id="form-global-error" class="text-center text-red-700 text-sm mb-2">
                <?php 
                if (!empty($form_errors)) {
                    echo implode('<br>', array_map('htmlspecialchars', $form_errors));
                }
                ?>
            </div>
            <div id="form-global-error" class="text-center text-red-700 text-sm mb-2"></div>
            <form class="con-form flex flex-col items-center justify-center w-full" method="POST" enctype="multipart/form-data" id="praticien-register-form" novalidate>
                <div id="part1" class="form-part">
                    <div class="mt-2 flex flex-col md:flex-row gap-4 w-full">
                        <div class="flex flex-col w-full md:w-1/2">
                            <p>Nom</p>
                            <input class="input-primary-small" type="text" name="lastname" placeholder="Doe" required/>
                            <span class="input-error text-xs text-red-700"></span>
                        </div>
                        <div class="flex flex-col w-full md:w-1/2">
                            <p>Prénom</p>
                            <input class="input-primary-small" type="text" name="firstname" placeholder="John" required/>
                            <span class="input-error text-xs text-red-700"></span>
                        </div>
                    </div>
                    <div class="mt-5 w-full">
                        <p>Email</p>
                        <input class="input-primary" type="email" name="email" placeholder="johndoe@exemple.com" required/>
                        <span class="input-error text-xs text-red-700"></span>
                    </div>
                    <div class="mt-5 w-full">
                        <p>Tel</p>
                        <input class="input-primary" type="num" name="tel" placeholder="+33 0 00 00 00 00" required/>
                        <span class="input-error text-xs text-red-700"></span>
                    </div>
                </div>
                <div id="part2" class="form-part">
                    <div class="mt-5 w-full">
                        <p>Siret</p>
                        <input class="input-primary" type="text" name="siret" placeholder="123 456 789 12345" required/>
                        <span class="input-error text-xs text-red-700"></span>
                    </div>
                    <div class="mt-5 w-full">
                        <p>Siret justificatif</p>
                        <input class="input-primary" type="file" name="siret_file" accept="image/jpeg, image/png, .pdf" required/>
                        <span class="input-error text-xs text-red-700"></span>
                    </div>
                    <div class="mt-5 w-full">
                        <p>Assurance</p>
                        <input class="input-primary" type="text" name="assurance" required/>
                        <span class="input-error text-xs text-red-700"></span>
                    </div>
                    <div class="mt-5 w-full">
                        <p>Assurance justificatif</p>
                        <input class="input-primary" type="file" name="assurance_file" accept="image/jpeg, image/png, .pdf" required/>
                        <span class="input-error text-xs text-red-700"></span>
                    </div>
                    <div class="mt-5 w-full">
                        <p>Expiration de l'assurance</p>
                        <input class="input-primary" type="date" name="assurance_exp" required/>
                        <span class="input-error text-xs text-red-700"></span>
                    </div>
                </div>
                <div id="part3" class="form-part w-full">
                    <div id="diploma-wrapper" class="flex flex-col gap-5 mt-5">
                        <label class="block font-semibold mb-2">Pratiques <span class="text-xs">(max <span id="diploma-limit-js"></span>)</span></label>
                    </div>
                    <button type="button" class="btn-primary mt-2 mb-2" id="diploma-add-btn">+ Ajouter une pratique</button>
                    <span class="input-error text-xs text-red-700"></span> 
                    <div>
                        <p><input type="checkbox" name="condition" required/> Je reconnais avoir lu et accepté sans réserve les <a class="bold underline" href="/help/convention" target="_blank">conditions de ce partenariat</a>. Je comprends que mon inscription ne pourra pas être validée sans cette acceptation.</p>
                        <span class="input-error text-xs text-red-700"></span>
                    </div>
                </div>
                <div class="flex flex-row gap-4 w-full justify-between mt-5">
                    <button class="btn-primary flex-1" id="back" type="button">Retour</button>
                    <button class="btn-primary flex-1" id="next" type="button">Suivant</button>
                    <button class="btn-primary flex-1" id="register" type="submit">Je m'inscris !</button>
                </div>
            </form>
            <a href="./praticien"><p class="text-center mt-4">J'ai déjà un compte</p></a>
        </div>
    </div>
</div>

<style>
.form-part {
    display: none;
}
#back {
    display: none;
}
#register {
    display: none;
}
.diploma-card {
    background-color: #f8fafc;
    border-radius: 0.5rem;
    padding: 1rem;
    box-shadow: 0 2px 8px #0001;
    margin-bottom: 0.25rem;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}
.input-error {
    min-height: 1.25rem;
    display: block;
}
</style>
