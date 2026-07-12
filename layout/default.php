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
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />

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
        --color-background: #d6eae7;
        --color-background-secondary: #73c5be;
    }
    @layer components {
        .btn-primary {
            @apply mt-5 bg-secondary text-white pt-2 pb-2 rounded-md w-80 text-center;
        }
        .input-primary {
            @apply mt-1 p-2 border-2 border-secondary rounded-sm w-80;
        }
        .input-primary-small {
            @apply mt-1 p-2 border-2 border-secondary rounded-sm w-38;
        }
        .center-box {
            @apply flex justify-center items-center h-screen;
        }
        .con-box-1 {
            @apply content-center bg-white pr-12 pl-12 p-5 shadow-lg rounded-lg;
        }
        .con-form {
            @apply flex flex-col mb-2;
        }

        @screen sm {
            .input-primary-small {
                @apply w-80;
            }
            .con-box-1 {
                @apply h-full w-full;
            }
            .con-box-1 {
                @apply rounded-xl;
            }
            .con-form {
                @apply justify-center items-center;
            }
        }

        /* Dashboard */
        .dashboard {
            @apply w-full min-h-screen flex justify-center items-center;
        }
        .dashboard-content {
            @apply flex flex-row w-[97vw] h-[90vh] rounded-lg shadow-lg items-stretch;
            background: #a3e3e3;
            background: linear-gradient(133deg, rgb(163, 210, 227) 0%, rgba(163, 227, 227, 1) 50%, rgb(183, 228, 228) 100%);
        }
        .side-bar {
            @apply flex flex-col h-full w-1/4 min-w-[300px] max-w-[500px] bg-white rounded-l-lg overflow-y-auto select-none;
        }
        .content {
            @apply flex flex-col h-[calc(100%-50px)] bg-white shadow-lg rounded-lg overflow-y-scroll overflow-x-hidden mt-auto mb-auto;

            scrollbar-width: none; 
            -ms-overflow-style: none;
            &::-webkit-scrollbar {
                display: none; 
            }
      
            @apply w-[80%] ml-[10px] mr-[10px];
        }
        
        .atelier {
            @apply bg-secondary p-2 mt-2 mr-5 text-white rounded-lg
        }
   

        /* Responsive Dashboard */
        @screen sm {
            .dashboard-content {
                @apply flex-col w-full h-full rounded-none;
            }
            .side-bar {
                @apply w-full min-w-0 max-w-full rounded-l-none rounded-t-lg;
            }
            .content {
                @apply w-full ml-0 rounded-lg rounded-t-none mt-0 mb-0;
            }
        }
 
        /* Balises */

        h1 {
            @apply text-2xl
        }

        h2 {
            @apply text-xl
        }

        button {
            @apply cursor-pointer
        }
        a {
            @apply text-primary cursor-pointer
        }
    }
</style>