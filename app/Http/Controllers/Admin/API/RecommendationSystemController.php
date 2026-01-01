<?php

namespace App\Http\Controllers\Admin\API;

use App\Exports\BooksExport;
use App\Exports\TransactionItemsExport;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Excel as MaatwebsiteExcel;
use Maatwebsite\Excel\Facades\Excel;

class RecommendationSystemController extends Controller
{
    public function train(Request $request)
    {
        $validated = $request->validate([
            'books_file' => 'nullable|file|mimes:xlsx,xls',
            'transactions_file' => 'nullable|file|mimes:xlsx,xls',
            'n_factors' => 'nullable|integer|min:1',
            'n_epochs' => 'nullable|integer|min:1',
            'lr_all' => 'nullable|numeric|min:0',
            'reg_all' => 'nullable|numeric|min:0',
        ]);

        try {
            $booksFile = $validated['books_file'] ?? null;
            $transactionsFile = $validated['transactions_file'] ?? null;
            $nFactors = $validated['n_factors'] ?? null;
            $nEpochs = $validated['n_epochs'] ?? null;
            $lrAll = $validated['lr_all'] ?? null;
            $regAll = $validated['reg_all'] ?? null;

            $client = new Client();
            $multipart = [
                // [
                //     'name'     => 'books_file',
                //     'contents' => fopen($booksFile->getRealPath(), 'r'),
                //     'filename' => $booksFile->getClientOriginalName(),
                // ],
                // [
                //     'name'     => 'transactions_file',
                //     'contents' => fopen($transactionsFile->getRealPath(), 'r'),
                //     'filename' => $transactionsFile->getClientOriginalName(),
                // ],
            ];

            if (!$booksFile) {
                // Export to public directory
                $booksExport = new BooksExport();
                $booksTempFilePath = storage_path('app/public/exports/books_export.xlsx');
                Excel::store(
                    export: $booksExport,
                    filePath: 'exports/books_export.xlsx',
                    disk: 'public',
                    writerType: MaatwebsiteExcel::XLSX

                );

                $multipart[] = [
                    'name'     => 'books_file',
                    'contents' => fopen($booksTempFilePath, 'r'),
                    'filename' => 'books_export.xlsx',
                ];
            } else {
                $multipart[] = [
                    'name'     => 'books_file',
                    'contents' => fopen($booksFile->getRealPath(), 'r'),
                    'filename' => $booksFile->getClientOriginalName(),
                ];
            }

            if (!$transactionsFile) {
                $transactionItemsExport = new TransactionItemsExport();
                $transactionsTempFilePath = storage_path('app/public/exports/transactions_export.xlsx');
                Excel::store(
                    export: $transactionItemsExport,
                    filePath: 'exports/transactions_export.xlsx',
                    disk: 'public',
                    writerType: MaatwebsiteExcel::XLSX
                );

                $multipart[] = [
                    'name'     => 'transactions_file',
                    'contents' => fopen($transactionsTempFilePath, 'r'),
                    'filename' => 'transactions_export.xlsx',
                ];
            } else {
                $multipart[] = [
                    'name'     => 'transactions_file',
                    'contents' => fopen($transactionsFile->getRealPath(), 'r'),
                    'filename' => $transactionsFile->getClientOriginalName(),
                ];
            }

            if (isset($nFactors)) {
                $multipart[] = [
                    'name'     => 'n_factors',
                    'contents' => $nFactors,
                ];
            }
            if (isset($nEpochs)) {
                $multipart[] = [
                    'name'     => 'n_epochs',
                    'contents' => $nEpochs,
                ];
            }
            if (isset($lrAll)) {
                $multipart[] = [
                    'name'     => 'lr_all',
                    'contents' => $lrAll,
                ];
            }
            if (isset($regAll)) {
                $multipart[] = [
                    'name'     => 'reg_all',
                    'contents' => $regAll,
                ];
            }

            $response = $client->post(env('RECOMMENDATION_SYSTEM_API_URL') . '/train', [
                'multipart' => $multipart,
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);

            return ResponseFormatter::success($responseData, 'Model trained successfully.', 201);
        } catch (Exception $e) {
            Log::error('Error training recommendation system: ' . $e->getMessage());
            return ResponseFormatter::error('Failed to train the model: ' . $e->getMessage(), 500);
        }
    }

    public function modelHistory()
    {
        try {
            $client = new Client();
            $response = $client->get(env('RECOMMENDATION_SYSTEM_API_URL') . '/model-history');

            $responseData = json_decode($response->getBody()->getContents(), true);

            return ResponseFormatter::success($responseData['models'], 'Model history retrieved successfully.');
        } catch (Exception $e) {
            Log::error('Error fetching model history: ' . $e->getMessage());
            return ResponseFormatter::error('Failed to fetch model history: ' . $e->getMessage(), 500);
        }
    }

    public function activateModel(Request $request, $modelId)
    {
        try {
            $client = new Client();
            $response = $client->post(env('RECOMMENDATION_SYSTEM_API_URL') . "/set-active-model/{$modelId}");

            $responseData = json_decode($response->getBody()->getContents(), true);

            return ResponseFormatter::success($responseData, 'Active model set successfully.');
        } catch (Exception $e) {
            Log::error('Error setting active model: ' . $e->getMessage());
            return ResponseFormatter::error('Failed to set active model: ' . $e->getMessage(), 500);
        }
    }

    public function activeModel()
    {
        try {
            $client = new Client();
            $response = $client->get(env('RECOMMENDATION_SYSTEM_API_URL') . '/active-model');

            $responseData = json_decode($response->getBody()->getContents(), true);

            return ResponseFormatter::success($responseData['active_model'], 'Active model retrieved successfully.');
        } catch (Exception $e) {
            Log::error('Error fetching active model: ' . $e->getMessage());
            return ResponseFormatter::error('Failed to fetch active model: ' . $e->getMessage(), 500);
        }
    }

    public function recommend(Request $request, $userId)
    {
        $limit = $request->input('limit', 10);

        try {
            $client = new Client();
            $response = $client->get(env('RECOMMENDATION_SYSTEM_API_URL') . "/recommend/{$userId}", [
                'query' => [
                    'limit' => $limit,
                ],
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);

            return ResponseFormatter::success($responseData, 'Recommendations retrieved successfully.');
        } catch (Exception $e) {
            Log::error('Error fetching recommendations: ' . $e->getMessage());
            return ResponseFormatter::error('Failed to fetch recommendations: ' . $e->getMessage(), 500);
        }
    }
}
