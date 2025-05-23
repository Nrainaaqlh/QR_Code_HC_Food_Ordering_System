<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit5d5b2c2947c4010e58a2e883d2905ef7
{
    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'Stripe\\' => 7,
        ),
        'P' => 
        array (
            'PHPMailer\\PHPMailer\\' => 20,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Stripe\\' => 
        array (
            0 => __DIR__ . '/..' . '/stripe/stripe-php/lib',
        ),
        'PHPMailer\\PHPMailer\\' => 
        array (
            0 => __DIR__ . '/..' . '/phpmailer/phpmailer/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit5d5b2c2947c4010e58a2e883d2905ef7::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit5d5b2c2947c4010e58a2e883d2905ef7::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit5d5b2c2947c4010e58a2e883d2905ef7::$classMap;

        }, null, ClassLoader::class);
    }
}
