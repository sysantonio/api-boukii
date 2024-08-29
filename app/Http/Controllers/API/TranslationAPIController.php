<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Class BookingController
 */

class TranslationAPIController extends AppBaseController
{

    public function __construct()
    {

    }

    /**
     * @OA\Post(
     *      path="/translate",
     *      summary="Traduce texto a un idioma especificado",
     *      tags={"Translation"},
     *      description="Envía texto para ser traducido al idioma especificado usando la API de DeepL.",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"text", "target_lang"},
     *              @OA\Property(property="text", type="string", example="Hello World!"),
     *              @OA\Property(property="target_lang", type="string", example="ES")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Operación exitosa",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="translations",
     *                  type="array",
     *                  @OA\Items(
     *                      @OA\Property(property="detected_source_language", type="string", example="EN"),
     *                      @OA\Property(property="text", type="string", example="¡Hola Mundo!")
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Solicitud incorrecta",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="error",
     *                  type="string",
     *                  example="El texto a traducir es obligatorio."
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Error interno del servidor",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="error",
     *                  type="string",
     *                  example="Ocurrió un error al realizar la traducción."
     *              )
     *          )
     *      )
     * )
     */
    public function translate(Request $request)
    {
        // Validar la solicitud entrante
        $request->validate([
            'text' => 'required|string',
            'target_lang' => 'required|string',
        ]);

        // Obtener la clave de API y la URL de la API de DeepL desde el archivo de entorno
        $deeplApiKey = env('DEEPL_API_KEY');
        $deeplApiUrl = env('DEEPL_API_URL');

        try {
            // Realizar la solicitud a la API de DeepL
            $response = Http::withHeaders([
                'Authorization' => 'DeepL-Auth-Key ' . $deeplApiKey,
            ])->post($deeplApiUrl, [
                'text' => $request->input('text'),
                'target_lang' => $request->input('target_lang'),
            ]);

            // Verificar si la solicitud fue exitosa
            if ($response->successful()) {
                return $this->sendResponse($response->json(), 'Translation retrieved successfully');
            } else {
                Log::error($response->getMessage(), $response->getTrace());
                return $this->sendError('Error retrieving Translation', 500);
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage(), $e->getTrace());
            return $this->sendError('Error retrieving Translation', 500);
        }
    }


}
