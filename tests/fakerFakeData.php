<?php

require dirname(__DIR__).'/vendor/autoload.php';

use Faker\Factory;

$separador = function(string $titulo) {
    echo "<br><b>" . str_repeat("=", 50) . "</b><br>";
    echo "<b>  $titulo</b><br>";
    echo "<b>" . str_repeat("=", 50) . "</b><br>";
};

// ─────────────────────────────────────────────
// CATALÀ (ca_ES)
// ─────────────────────────────────────────────
$separador("CATALÀ (ca_ES)");
$faker = Factory::create('ca_ES');

echo "<br><b>--- Nom i dades personals ---</b><br>";
echo "Nom:       " . $faker->name() . "<br>";
echo "Masculí:   " . $faker->name('male') . "<br>";
echo "Femení:    " . $faker->name('female') . "<br>";
echo "Empresa:   " . $faker->company() . "<br>";
echo "Email:     " . $faker->email() . "<br>";
echo "Telèfon:   " . $faker->phoneNumber() . "<br>";

echo "<br><b>--- Adreça ---</b><br>";
echo $faker->address() . "<br>";
echo "Ciutat:    " . $faker->city() . "<br>";
echo "CP:        " . $faker->postcode() . "<br>";

echo "<br><b>--- Text (NO és Lorem Ipsum) ---</b><br>";
echo "Frase:     " . $faker->sentence() . "<br>";
echo "<br>Paràgraf:<br>" . $faker->paragraph(4) . "<br>";
echo "<br><br><br><br>";
echo "<br>Text llarg:<br>" . $faker->text(500) . "<br>";

echo "<br><b>--- 3 paràgrafs ---</b><br>";
$paragraphs = $faker->paragraphs(3, true);
echo "<br><b>Aquest Paragraph realista:</b> $paragraphs";