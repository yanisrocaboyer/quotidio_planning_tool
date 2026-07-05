<?php
$TITLE = "Quotidio Planning - Connexion";
?>
<div class="center-box">
    <div class="con-box-1">
        <img class="w-80 ml-auto mr-auto" src="/assets/quotidio_big_logo.png" alt="quotidio_big_logo" draggable="false">
        <div>
            <h1 class="mt-2 text-2xl text-center text-primary">Inscription praticien·ne</h1>
            <form class="con-form">
                <div id="part1">
                    <div class="mt-2 flex gap-4">
                        <div class="flex flex-col">
                            <p>Nom</p>
                            <input class="input-primary-small" type="text" placeholder="Doe"/>
                        </div>
                        <div class="flex flex-col">
                            <p>Prénom</p>
                            <input class="input-primary-small" type="text" placeholder="John"/>
                        </div>
                    </div>
                    <div class="mt-5">
                        <p>Email</p>
                        <input class="input-primary" type="email" placeholder="johndoe@exemple.com"/>
                    </div>
                    <div class="mt-5">
                        <p>Tel</p>
                        <input class="input-primary" type="num" placeholder="+33 0 00 00 00 00"/>
                    </div>
                </div>
                <div id="part2">
                    <div class="mt-5">
                        <p>Siret</p>
                        <input class="input-primary" type="text" placeholder="123 456 789 12345"/>
                    </div>
                    <div class="mt-5">
                        <p></p>
                        <input class="input-primary" type="text" placeholder="johndoe@exemple.com"/>
                    </div>
                </div>
            <a class="btn-primary" id="next"><button >Suivant</button></a>
            <a class="btn-primary" id="back"><button>Retour</button></a>
            <a class="btn-primary" href="#" id="register"><button>Inscription</button></a>
            </form>
            <a href="./praticien"><p class="text-center">J'ai déjà un compte</p></a><br>
        </div>
    </div>
</div>

<style>
#part2 {
    display: none;
}
#back {
    display: none;
}
#register {
    display: none;
}
</style>

<script>

const part1 = document.getElementById("part1")
const part2 = document.getElementById("part2");

const next = document.getElementById("next");
const back = document.getElementById("back");
const register = document.getElementById("register");

next.addEventListener('click', function(event) {
    event.preventDefault();

    part2.style.display = 'block';
    back.style.display = 'inline-block';
    register.style.display = 'inline-block';

    if (part1) part1.style.display = 'none';
    next.style.display = 'none';
});

back.addEventListener('click', function(event) {
    event.preventDefault();

    part1.style.display = 'block';
    next.style.display = 'inline-block';

    if (part2) part2.style.display = 'none';
    back.style.display = 'none';
    register.style.display = 'none';
});


</script>