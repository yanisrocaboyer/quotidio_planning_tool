document.addEventListener("click", function (e) {
    const link = e.target.closest("a");

    if (
        link &&
        link.href &&
        link.host === window.location.host &&
        !link.classList.contains("refresh") 
    ) {
        if (link.target === "_blank") return;

        e.preventDefault();

        const url = link.href;

        loadPage(url);
    }
});

function loadPage(url) {
    fetch(url, {
        headers: {
            "X-Requested-With": "XMLHttpRequest"
        }
    })
    .then(response => {
        if (!response.ok) throw new Error("Page introuvable");
        return response.text();
    })
    .then(html => {
        const contentContainer = document.getElementById("content");     
        contentContainer.innerHTML = html;

        const scripts = contentContainer.querySelectorAll("script");
        scripts.forEach(oldScript => {
            const newScript = document.createElement("script");
            
            Array.from(oldScript.attributes).forEach(attr => {
                newScript.setAttribute(attr.name, attr.value);
            });
            
            newScript.appendChild(document.createTextNode(oldScript.innerHTML));
            oldScript.parentNode.replaceChild(newScript, oldScript);
        });

        window.history.pushState({ path: url }, "", url);
        
        if (typeof window.initThemeSystem === "function") {
            window.initThemeSystem();
        }
    })
    .catch(error => {
        console.error("Erreur de chargement :", error);
    });
}

window.addEventListener("popstate", function () {
    loadPage(window.location.href);
});