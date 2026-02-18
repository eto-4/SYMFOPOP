<?php
require dirname(__DIR__).'/vendor/autoload.php';

$faker = Faker\Factory::create();
$uuid = $faker->uuid();
$imageUrl = 'https://picsum.photos/seed/'.$uuid.'/600/400';

// echo "URL de prueba: $imageUrl\n";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <main>
        <span>URL LINK</span>
        <a href="<?= $imageUrl ?>"><?= $imageUrl ?></a>
    </main>
</body>
</html>