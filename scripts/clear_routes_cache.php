<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔄 Limpiando cache de rutas...\n";

// Clear route cache
\Illuminate\Support\Facades\Artisan::call('route:clear');
echo "✅ Cache de rutas limpiado\n";

// Clear config cache
\Illuminate\Support\Facades\Artisan::call('config:clear');
echo "✅ Cache de configuración limpiado\n";

// Clear application cache
\Illuminate\Support\Facades\Artisan::call('cache:clear');
echo "✅ Cache de aplicación limpiado\n";

// Optimize for production
\Illuminate\Support\Facades\Artisan::call('optimize:clear');
echo "✅ Optimización limpiada\n";

echo "\n🎉 Todos los caches limpiados exitosamente!\n";
echo "El error de api_v5.php debería estar resuelto.\n";