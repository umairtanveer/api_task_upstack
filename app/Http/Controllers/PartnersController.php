<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

class PartnersController extends Controller
{

    protected const API_URL = 'https://api.filtered.ai/q/get-partner-availability';

    /**
     * Get Partners record from external API
     *
     * @return JsonResponse
     */
    public function getPartners(): JsonResponse
    {
        try {
            $response = Http::get(self::API_URL);

            # Check if response received with status code 200
            if ($response->status() !== 200) {
                return response()->json([
                    'status' => false,
                    'message' => 'Error'
                ]);
            }

            $response = json_decode($response->body());

            if (empty($response->availability) || !isset($response->availability)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Error'
                ]);
            }

            $output = [];

            foreach ($response->availability as $data) {
                $index = 0;

                if (!empty($output[$data->partner->country])) {
                    $index = count($output[$data->partner->country]) + 1;

                    if (!in_array($data->date, $output[$data->partner->country])) {
                        $output[$data->partner->country][$index] = $data->date;
                    }
                } else {
                    $output[$data->partner->country][$index] = $data->date;
                }
            }

            # Sort according to calender : means (ASC)
            $data = [];
            foreach ($output as $key => $value) {
                sort($output[$key]);
                $data[$key] = $output[$key];
            }

            return response()->json([
                'success' => true,
                'output' => $data
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'status' => false,
                'message' => $ex->getMessage()
            ]);
        }
    }
}
