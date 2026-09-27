<?php
$TITLE = "Quotidio Planning";
require_once './assets/js/praticien_register.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../assets/favicon.ico" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link rel="stylesheet" href="/assets/style/output.css?v=<?php echo filemtime('assets/style/output.css'); ?>">
    <title><?php echo $TITLE; ?></title>
</head>
<body style="background-image: url('/assets/bg.png')" class="bg-no-repeat bg-bottom bg-cover">
    <main id="content">
        <?php 
            if (isset($page_content)) {
                echo $page_content;
            } else {
                if (file_exists("page/$url")) {
                    require "page/$url";
                } else {
                    require "page/error/404.php";
                }
            }
       
        ?>   
    </main>
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        function getRowCards(card) {
            var grid = card.closest(".grid");
            if (!grid) return [card];
            var cards = Array.prototype.slice.call(grid.querySelectorAll(".atelier-clickable"));
            var gridTop = grid.getBoundingClientRect().top;
            var rowTop = Math.round(card.getBoundingClientRect().top - gridTop);
            return cards.filter(function (c) {
                if (c.style.display === "none") return false;
                return Math.abs(Math.round(c.getBoundingClientRect().top - gridTop) - rowTop) < 8;
            });
        }

        // Déployer / replier les ateliers au clic (toute la ligne)
        document.addEventListener("click", function (e) {
            var card = e.target.closest(".atelier-clickable");
            if (!card) return;
            if (e.target.closest("a, button, select, input, .atelier-actions")) return;

            var deploy = !card.classList.contains("deployed");
            getRowCards(card).forEach(function (c) {
                c.classList.toggle("deployed", deploy);
            });
        });

        // Filtres par section
        function applyAtelierFilters(section) {
            var praticien = "";
            var client = "";
            var date = "";
            var type = "";
            document.querySelectorAll('.atelier-filter-select[data-section="' + section + '"]').forEach(function (el) {
                if (el.dataset.filter === "praticien") praticien = el.value;
                else if (el.dataset.filter === "client") client = el.value;
                else if (el.dataset.filter === "date") date = el.value;
                else if (el.dataset.filter === "type") type = el.value;
            });
            document.querySelectorAll('.atelier-filter-item[data-filter-section="' + section + '"]').forEach(function (card) {
                var show = true;
                if (praticien && String(card.dataset.praticien) !== praticien) show = false;
                if (client && String(card.dataset.client) !== client) show = false;
                if (date && card.dataset.date !== date) show = false;
                if (type && card.dataset.type !== type) show = false;
                card.style.display = show ? "" : "none";
            });
        }

        document.querySelectorAll('.atelier-filter-select').forEach(function (el) {
            el.addEventListener("change", function () {
                applyAtelierFilters(el.dataset.section);
            });
        });
    });
    </script>
</body>
</html>