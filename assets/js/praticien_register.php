<script>
document.addEventListener('DOMContentLoaded', function() {
    const parts = Array.from(document.querySelectorAll('.form-part'));
    let currentPartIdx = 0;

    const nextBtn = document.getElementById("next");
    const backBtn = document.getElementById("back");
    const registerBtn = document.getElementById("register");
    const form = document.getElementById("praticien-register-form");
    const globalError = document.getElementById('form-global-error');

    const DIPLOMA_LIMIT = <?php echo json_encode($DIPLOMA_LIMIT); ?>;
    const diplomaWrapper = document.getElementById('diploma-wrapper');
    const diplomaAddBtn = document.getElementById('diploma-add-btn');
    const diplomaLimitJs = document.getElementById('diploma-limit-js');
    if (diplomaLimitJs) diplomaLimitJs.textContent = DIPLOMA_LIMIT;

    let diplomaCount = 0;

    function clearErrors(part) {
        if (!part) return;
        part.querySelectorAll('.input-error').forEach(err => { err.textContent = ''; });
    }

    function clearGlobalError() {
        globalError.textContent = '';
    }

    function showError(input, msg) {
        const error = input.parentNode.querySelector('.input-error');
        if (error) error.textContent = msg;
    }

    function validatePart(idx) {
        const part = parts[idx];
        clearErrors(part);

        let valid = true;

        if (idx === 0) {
            // Nom
            let lastname = part.querySelector('input[name="lastname"]');
            if (!lastname.value.trim()) {
                valid = false;
                showError(lastname, 'Veuillez remplir le nom.');
            }
            // Prénom
            let firstname = part.querySelector('input[name="firstname"]');
            if (!firstname.value.trim()) {
                valid = false;
                showError(firstname, 'Veuillez remplir le prénom.');
            }
            // Email
            let email = part.querySelector('input[name="email"]');
            if (!email.value.trim()) {
                valid = false;
                showError(email, 'Veuillez remplir l\'email.');
            } else {
                // Simple email format check
                const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!pattern.test(email.value.trim())) {
                    valid = false;
                    showError(email, 'Format d\'email invalide.');
                }
            }
            // Tel
            let tel = part.querySelector('input[name="tel"]');
            if (!tel.value.trim()) {
                valid = false;
                showError(tel, 'Veuillez remplir le téléphone.');
            }
        } else if (idx === 1) {
            // Siret
            let siret = part.querySelector('input[name="siret"]');
            if (!siret.value.trim()) {
                valid = false;
                showError(siret, 'Veuillez remplir le siret.');
            }
            // Siret file
            let siretFile = part.querySelector('input[name="siret_file"]');
            if (siretFile && (!siretFile.files || siretFile.files.length === 0)) {
                valid = false;
                showError(siretFile, 'Veuillez joindre le justificatif de siret.');
            }
            // Assurance
            let assurance = part.querySelector('input[name="assurance"]');
            if (!assurance.value.trim()) {
                valid = false;
                showError(assurance, 'Veuillez remplir l\'assurance.');
            }
            // Assurance file
            let assuranceFile = part.querySelector('input[name="assurance_file"]');
            if (assuranceFile && (!assuranceFile.files || assuranceFile.files.length === 0)) {
                valid = false;
                showError(assuranceFile, 'Veuillez joindre le justificatif d\'assurance.');
            }
        } else if (idx === 2) {
            clearErrors(part);

            let diplomas = diplomaWrapper.querySelectorAll('.diploma-card');
            if (diplomas.length < 1) {
                valid = false;
                let lastError = part.querySelector('.input-error');
                if (lastError) lastError.textContent = "Ajoutez au moins une pratique.";
            } else {
                diplomas.forEach(card => {
                    // Pratique name
                    let inputName = card.querySelector('input[name="diploma_name[]"]');
                    if (!inputName.value.trim()) {
                        valid = false;
                        showError(inputName, 'Veuillez entrer le nom de la pratique.');
                    }
                    // Justificatif file
                    let inputFile = card.querySelector('input[name="diploma_file[]"]');
                    if (!inputFile.files || inputFile.files.length === 0) {
                        valid = false;
                        showError(inputFile, 'Veuillez joindre un fichier.');
                    }
                });
            }
        }

        return valid;
    }

    function validateAllParts() {
        let isValid = true;
        clearGlobalError();
        for (let i = 0; i < parts.length; i++) {
            const local = validatePart(i);
            isValid = isValid && local;
        }
        return isValid;
    }

    function createDiplomaInput(idx) {
        const group = document.createElement('div');
        group.className = "diploma-card";
        group.dataset.diplomaIdx = idx;

        const nameDiv = document.createElement('div');
        nameDiv.className = "flex flex-col";
        const labelName = document.createElement('label');
        labelName.textContent = "Nom ";
        const inputName = document.createElement('input');
        inputName.className = "input-primary";
        inputName.type = "text";
        inputName.name = "diploma_name[]";
        inputName.required = true;
        const errorName = document.createElement('span');
        errorName.className = "input-error text-xs text-red-700";
        nameDiv.appendChild(labelName);
        nameDiv.appendChild(inputName);
        nameDiv.appendChild(errorName);

        const fileDiv = document.createElement('div');
        fileDiv.className = "flex flex-col";
        const labelFile = document.createElement('label');
        labelFile.textContent = "Diplôme/Certification (.jpeg/.png/.pdf)";
        const inputFile = document.createElement('input');
        inputFile.className = "input-primary";
        inputFile.type = "file";
        inputFile.name = "diploma_file[]";
        inputFile.required = true;
        inputFile.accept = "image/jpeg, image/png, .pdf";
        const errorFile = document.createElement('span');
        errorFile.className = "input-error text-xs text-red-700";
        fileDiv.appendChild(labelFile);
        fileDiv.appendChild(inputFile);
        fileDiv.appendChild(errorFile);

        const removeBtn = document.createElement('button');
        removeBtn.type = "button";
        removeBtn.textContent = "Supprimer";
        removeBtn.className = "btn-primary w-32 p-1 mt-1 text-xs";
        removeBtn.onclick = function(ev) {
            ev.preventDefault();
            group.remove();
            diplomaCount = Math.max(0, diplomaCount - 1);
            if (diplomaAddBtn.disabled && diplomaCount < DIPLOMA_LIMIT) {
                diplomaAddBtn.disabled = false;
            }
        };

        group.appendChild(nameDiv);
        group.appendChild(fileDiv);
        group.appendChild(removeBtn);

        return group;
    }

    function addDiplomaInput() {
        if (diplomaCount < DIPLOMA_LIMIT) {
            const idx = diplomaCount;
            const diplomaElem = createDiplomaInput(idx);
            diplomaWrapper.appendChild(diplomaElem);
            diplomaCount++;
            if (diplomaCount >= DIPLOMA_LIMIT) {
                diplomaAddBtn.disabled = true;
            }
        }
    }

    diplomaAddBtn.addEventListener('click', function(e) {
        e.preventDefault();
        addDiplomaInput();
    });

    addDiplomaInput();

    function updatePartsDisplay() {
        parts.forEach((part, idx) => {
            part.style.display = (idx === currentPartIdx) ? "block" : "none";
        });

        if (currentPartIdx === 0) {
            backBtn.style.display = "none";
            nextBtn.style.display = parts.length > 1 ? "inline-block" : "none";
            registerBtn.style.display = parts.length === 1 ? "inline-block" : "none";
        } else if (currentPartIdx === parts.length - 1) {
            backBtn.style.display = "inline-block";
            nextBtn.style.display = "none";
            registerBtn.style.display = "inline-block";
        } else {
            backBtn.style.display = "inline-block";
            nextBtn.style.display = "inline-block";
            registerBtn.style.display = "none";
        }
    }

    nextBtn.addEventListener('click', function(event) {
        event.preventDefault();
        clearGlobalError();
        const valid = validatePart(currentPartIdx);
        if (valid) {
            if (currentPartIdx < parts.length - 1) {
                currentPartIdx++;
                updatePartsDisplay();
            }
        } else {
            globalError.textContent = "Veuillez remplir tous les champs obligatoires de cette partie avant de continuer.";
        }
    });

    backBtn.addEventListener('click', function(event) {
        event.preventDefault();
        clearGlobalError();
        if (currentPartIdx > 0) {
            currentPartIdx--;
            updatePartsDisplay();
        }
    });

    form.addEventListener('submit', function(event) {
        clearGlobalError();
        if (!validateAllParts()) {
            event.preventDefault();
            globalError.textContent = "Veuillez remplir correctement tous les champs du formulaire (au moins un diplôme/certification doit être ajouté).";
            for(let i=0; i<parts.length; i++) {
                if (!validatePart(i)) {
                    currentPartIdx = i;
                    updatePartsDisplay();
                    break;
                }
            }
        } else {
            clearGlobalError();
        }
    });

    updatePartsDisplay();
});
</script>