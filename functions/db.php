<?php
$sql = new PDO(
    'mysql:host='. $_ENV["HOST"] .':' .$_ENV["PORT"] .';dbname='. $_ENV["DATABASE"] .';charset=utf8',
    $_ENV["USERNAME"],
    $_ENV["PASSWORD"]
);