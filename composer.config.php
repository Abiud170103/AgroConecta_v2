<?php
/**
 * Archivo de configuración de composer para AgroConecta
 * Define dependencias y autoloading
 */

$composer_config = [
    "name" => "escom/agroconecta",
    "description" => "Sistema de apoyo a agricultores locales",
    "version" => "1.0.0",
    "type" => "project",
    "authors" => [
        [
            "name" => "Equipo AgroConecta 6CV1",
            "email" => "agroconecta@escom.ipn.mx"
        ]
    ],
    "require" => [
        "php" => ">=8.0",
        "phpmailer/phpmailer" => "^6.8",
        "mercadopago/dx-php" => "^2.5"
    ],
    "autoload" => [
        "psr-4" => [
            "AgroConecta\\Controllers\\" => "app/controllers/",
            "AgroConecta\\Models\\" => "app/models/",
            "AgroConecta\\Core\\" => "app/core/"
        ]
    ],
    "config" => [
        "optimize-autoloader" => true
    ]
];

// Este archivo puede ser usado para generar composer.json
// file_put_contents(__DIR__ . '/composer.json', json_encode($composer_config, JSON_PRETTY_PRINT));
?>