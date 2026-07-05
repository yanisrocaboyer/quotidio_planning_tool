
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
            require "page/$url";
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
            @apply mt-1 p-2 border-2 border-secondary rounded-sm w-38;
        }
        .center-box {
            @apply flex justify-center items-center h-screen;
        }
        .con-box-1 {
            @apply content-center bg-white p-12 rounded-xl shadow-lg max-sm:h-full max-sm:w-full;
        }
        .con-form {
            @apply flex flex-col mb-2 max-sm:justify-center max-sm:items-center
        }
        button {
            @apply cursor-pointer
        }
        a {
            @apply text-primary cursor-pointer
        }
    }
</style>