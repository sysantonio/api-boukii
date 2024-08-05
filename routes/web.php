<?php

use Illuminate\Support\Facades\Route;
use Faker\Factory as Faker;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

/*function sendPostRequest($url, $data, $proxy = null, $proxyAuth = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Aumentar tiempo de espera

    if ($proxy) {
        curl_setopt($ch, CURLOPT_PROXY, $proxy);
        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
        if ($proxyAuth) {
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyAuth);
        }
    }

    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);

    // Capturar el encabezado de la solicitud y respuesta para depuración
    curl_setopt($ch, CURLINFO_HEADER_OUT, true);
    curl_setopt($ch, CURLOPT_HEADER, true);

    // Ejecutar la solicitud
    $response = curl_exec($ch);

    // Verificar si hubo un error
    if (curl_errno($ch)) {
        $error_msg = curl_error($ch);
        curl_close($ch);
        return "cURL Error: $error_msg";
    }

    // Obtener información sobre la solicitud
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $request_header = curl_getinfo($ch, CURLINFO_HEADER_OUT);
    $response_header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $response_header = substr($response, 0, $response_header_size);
    $response_body = substr($response, $response_header_size);

    curl_close($ch);

    // Devolver la información detallada
    return [
        'HTTP Code' => $http_code,
        'Request Header' => $request_header,
        'Response Header' => $response_header,
        'Response Body' => $response_body
    ];
}/*

function getImageBase64FromUrl($url) {
    $context = stream_context_create([
        'http' => [
            'header' => 'User-Agent: PHP'
        ]
    ]);

    $imageData = file_get_contents($url, false, $context);
    if ($imageData === false) {
        throw new Exception("Unable to fetch image from URL: $url");
    }
    return base64_encode($imageData);
}

function generateRandomEmail() {
    $faker = Faker::create();
    $domains = ['outlook.com', 'icloud.com', 'gmail.com'];
    $email = $faker->userName . '@' . $domains[array_rand($domains)];
    return $email;
}

Route::get('/boom', function () {
    $url = 'https://data-hub-service.test.bike-on.app/applicationusers';
    $proxy = '207.244.217.165:6712'; // Ajusta el puerto según sea necesario
    $proxyAuth = 'rkmtqyok:adkuxl3pb2ph';

    // Verifica si el proxy está funcionando
    if (!checkProxy($proxy, $proxyAuth)) {
        echo "Proxy not working. Please check the proxy settings.";
        return;
    }

    for ($i = 0; $i < 5; $i++) {
        $faker = Faker::create();
        $email = generateRandomEmail();
        $password = $faker->password;
        $imagedata = getImageBase64FromUrl('https://upload.wikimedia.org/wikipedia/commons/6/69/Seljalandsfoss%2C_Su%C3%B0urland%2C_Islandia%2C_2014-08-16%2C_DD_201-203_HDR.JPG');
        $data = [
            'firstname' => $faker->firstName,
            'lastname' => $faker->lastName,
            'email' => $email,
            'imagedata' => $imagedata,
            'password' => $password,
            'confirmpassword' => $password
        ];

        $response = sendPostRequest($url, $data, $proxy, $proxyAuth);
        $response = sendPostRequest($url, $data, $proxy, $proxyAuth);
        echo "Sent request $i with email $email:\n";
        echo "HTTP Code: " . $response['HTTP Code'] . "\n";
        echo "Request Header: " . $response['Request Header'] . "\n";
        echo "Response Header: " . $response['Response Header'] . "\n";
        echo "Response Body: " . $response['Response Body'] . "\n\n";
        // Optional sleep to prevent overwhelming the server
        // usleep(500000); // Sleep for 0.5 seconds
    }
});
function checkProxy($proxy, $proxyAuth = null) {
    $url = "https://www.google.com";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_PROXY, $proxy);
    curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);

    if ($proxyAuth) {
        curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyAuth);
    }

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return $http_code == 200;
}*/

Route::get('logs', [\Rap2hpoutre\LaravelLogViewer\LogViewerController::class, 'index']);
