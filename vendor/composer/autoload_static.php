<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitc725156a60ae924a5d88dfbd50c82b5c
{
    public static $prefixLengthsPsr4 = array (
        'T' => 
        array (
            'Twilio\\' => 7,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Twilio\\' => 
        array (
            0 => __DIR__ . '/..' . '/twilio/sdk/src/Twilio',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitc725156a60ae924a5d88dfbd50c82b5c::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitc725156a60ae924a5d88dfbd50c82b5c::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitc725156a60ae924a5d88dfbd50c82b5c::$classMap;

        }, null, ClassLoader::class);
    }
}
