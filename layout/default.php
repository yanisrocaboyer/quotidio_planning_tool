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
</body>
</html>