<?php
$TITLE = "Quotidio Planning";
require_once './assets/js/praticien_register.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="shortcut icon" href="../assets/favicon.ico" type="image/x-icon">
    <title><?php echo $TITLE; ?></title>
</head>
<body class="bg-[url('../assets/bg.png')] bg-no-repeat bg-bottom bg-cover">
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
    <script src="/assets/js/router.js"></script>
</body>
</html>

<style type="text/tailwindcss">
    @theme {
        --color-primary: #17365d;
        --color-secondary: #45667d;
    }
    @layer components {
        .btn-primary {
            @apply mt-5 bg-secondary text-white pt-2 pb-2 rounded-md w-80 text-center;
        }
        .input-primary {
            @apply mt-1 p-2 border-2 border-secondary rounded-sm w-80;
        }
        .input-primary-small {
            @apply mt-1 p-2 border-2 border-secondary rounded-sm w-38 max-sm:w-80;
        }
        .center-box {
            @apply flex justify-center items-center h-screen;
        }
        .con-box-1 {
            @apply content-center bg-white pr-12 pl-12 p-5 shadow-lg max-sm:h-full max-sm:w-full sm:rounded-xl;
        }
        .con-form {
            @apply flex flex-col mb-2 max-sm:justify-center max-sm:items-center
        }

        /* Dashboard */
        .dashboard {
            @apply w-full h-full bg-white/20 flex flex-row
        }
        .side-bar {
            @apply m-5 bg-white/80 h-[95%] rounded-sm flex flex-col w-1/4 min-w-[200px] max-w-[500px];
        }
        .content {
            @apply mt-5 mr-5 bg-white/70 h-[95%] rounded-sm flex flex-col flex-1;
        }
   
        button {
            @apply cursor-pointer
        }
        a {
            @apply text-primary cursor-pointer
        }
    }
</style>