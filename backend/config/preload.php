<?php

// Ruta al archivo de preloading generado por Symfony
$preloadFile = dirname(__DIR__).'/var/cache/prod/App_KernelProdContainer.preload.php';

// Precargar solo si existe
if (file_exists($preloadFile) && is_readable($preloadFile)) {
    require $preloadFile;
}

// Opcional: precargar clases críticas adicionales para mejorar rendimiento
if (function_exists('opcache_compile_file')) {
    $criticalClasses = [
        // Symfony core
        'Symfony\Component\HttpKernel\Kernel',
        'Symfony\Component\DependencyInjection\ContainerInterface',
        'Symfony\Component\HttpFoundation\Request',
        'Symfony\Component\HttpFoundation\Response',

        // Bundles principales
        'ApiPlatform\Symfony\Bundle\ApiPlatformBundle',
        'Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManager',

        // Entidades críticas
        'App\Entity\User',
        'App\Entity\Student',
    ];

    foreach ($criticalClasses as $class) {
        if (class_exists($class)) {
            opcache_compile_file((new \ReflectionClass($class))->getFileName());
        }
    }
}
