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
        document.getElementById("content").innerHTML = html;

        window.history.pushState({ path: url }, "", url);
        
    })
    .catch(error => {
        console.error("Erreur de chargement :", error);
    });
}

window.addEventListener("popstate", function () {
    loadPage(window.location.href);
});