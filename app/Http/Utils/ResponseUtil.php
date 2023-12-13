<?php

namespace App\Http\Utils;

class ResponseUtil
{
    public static function makeResponse(string $message, mixed $data): array
    {
        if ($data instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            // Extraer datos de paginación
            $paginationData = $data->toArray();

            // Extraer los elementos (items) del paginador
            $items = $paginationData['data'];

            // Eliminar el elemento 'data' para evitar duplicación
            unset($paginationData['data']);

            // Combinar los elementos con los datos de paginación
            return [
                'success' => true,
                'data' => $items,
                'pagination' => $paginationData,
                'message' => $message,
            ];
        }

        // Si $data no es un paginador, simplemente envolverlo en 'data'
        return [
            'success' => true,
            'data' => $data,
            'message' => $message,
        ];
    }

    public static function makeError(string $message, array $data = []): array
    {
        $res = [
            'success' => false,
            'message' => $message,
        ];

        if (!empty($data)) {
            $res['data'] = $data;
        }

        return $res;
    }
}
