<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitf6d89f8e667bfb35a87876e84dfe07fa
{
    public static $prefixesPsr0 = array (
        'I' => 
        array (
            'InDemandDigital\\IDDFramework' => 
            array (
                0 => __DIR__ . '/../..' . '/src',
            ),
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixesPsr0 = ComposerStaticInitf6d89f8e667bfb35a87876e84dfe07fa::$prefixesPsr0;

        }, null, ClassLoader::class);
    }
}
