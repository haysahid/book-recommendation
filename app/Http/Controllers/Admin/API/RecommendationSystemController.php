<?php

namespace App\Http\Controllers\Admin\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Repositories\RecommendationSystemRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RecommendationSystemController extends Controller
{
    protected RecommendationSystemRepository $recommendationSystemRepository;

    public function __construct()
    {
        $this->recommendationSystemRepository = new RecommendationSystemRepository();
    }

    public function trainModel(Request $request)
    {
        $validated = $request->validate([
            'books_file' => 'nullable|file|mimes:xlsx,xls',
            'transactions_file' => 'nullable|file|mimes:xlsx,xls',
            'n_factors' => 'nullable|integer|min:1',
            'n_epochs' => 'nullable|integer|min:1',
            'lr_all' => 'nullable|numeric|min:0',
            'reg_all' => 'nullable|numeric|min:0',
            'reference' => 'nullable|string',
        ]);

        try {
            $responseData = $this->recommendationSystemRepository->trainModel(
                booksFile: $validated['books_file'] ?? null,
                transactionsFile: $validated['transactions_file'] ?? null,
                reference: $validated['reference'] ?? null,
                nFactors: $validated['n_factors'] ?? null,
                nEpochs: $validated['n_epochs'] ?? null,
                lrAll: $validated['lr_all'] ?? null,
                regAll: $validated['reg_all'] ?? null,
                createdBy: 'manual',
            );

            return ResponseFormatter::success($responseData, 'Model trained successfully.', 201);
        } catch (Exception $e) {
            Log::error('Error training recommendation system: ' . $e->getMessage());
            return ResponseFormatter::error('Failed to train the model: ' . $e->getMessage(), 500);
        }
    }

    public function tuneModel(Request $request)
    {
        $validated = $request->validate([
            'books_file' => 'nullable|file|mimes:xlsx,xls',
            'transactions_file' => 'nullable|file|mimes:xlsx,xls',
            'reference' => 'nullable|string',
            'n_factors' => 'nullable|array',
            'n_factors.*' => 'integer|min:1',
            'n_epochs' => 'nullable|array',
            'n_epochs.*' => 'integer|min:1',
            'lr_all' => 'nullable|array',
            'lr_all.*' => 'numeric|min:0',
            'reg_all' => 'nullable|array',
            'reg_all.*' => 'numeric|min:0',
            'cv' => 'nullable|integer|min:2',
            'n_jobs' => 'nullable|integer',
        ]);

        Log::info('Tuning model with parameters: ' . json_encode($validated));

        try {
            $paramGrid = [
                'n_factors' => isset($validated['n_factors']) ? array_map('intval', $validated['n_factors']) : null,
                'n_epochs' => isset($validated['n_epochs']) ? array_map('intval', $validated['n_epochs']) : null,
                'lr_all' => isset($validated['lr_all']) ? array_map('floatval', $validated['lr_all']) : null,
                'reg_all' => isset($validated['reg_all']) ? array_map('floatval', $validated['reg_all']) : null,
            ];

            $responseData = $this->recommendationSystemRepository->tuneModel(
                booksFile: $validated['books_file'] ?? null,
                transactionsFile: $validated['transactions_file'] ?? null,
                reference: $validated['reference'] ?? null,
                paramGrid: $paramGrid,
                cv: $validated['cv'] ?? null,
                nJobs: $validated['n_jobs'] ?? null,
            );

            return ResponseFormatter::success($responseData['result'], 'Model tuned successfully.');
        } catch (Exception $e) {
            Log::error('Error tuning model: ' . $e->getMessage());
            return ResponseFormatter::error('Failed to tune the model: ' . $e->getMessage(), 500);
        }
    }

    public function modelHistory()
    {
        try {
            $responseData = $this->recommendationSystemRepository->getModelHistory();
            return ResponseFormatter::success($responseData['models'], 'Model history retrieved successfully.');
        } catch (Exception $e) {
            Log::error('Error fetching model history: ' . $e->getMessage());
            return ResponseFormatter::error('Failed to fetch model history: ' . $e->getMessage(), 500);
        }
    }

    public function activateModel(Request $request, $modelId)
    {
        try {
            $responseData = $this->recommendationSystemRepository->setActiveModel($modelId);
            return ResponseFormatter::success($responseData, 'Active model set successfully.');
        } catch (Exception $e) {
            Log::error('Error setting active model: ' . $e->getMessage());
            return ResponseFormatter::error('Failed to set active model: ' . $e->getMessage(), 500);
        }
    }

    public function activeModel()
    {
        try {
            $responseData = $this->recommendationSystemRepository->getActiveModel();
            return ResponseFormatter::success($responseData['active_model'], 'Active model retrieved successfully.');
        } catch (Exception $e) {
            Log::error('Error fetching active model: ' . $e->getMessage());
            return ResponseFormatter::error('Failed to fetch active model: ' . $e->getMessage(), 500);
        }
    }

    public function deleteModel(Request $request, $modelId)
    {
        try {
            $responseData = $this->recommendationSystemRepository->deleteModel($modelId);
            return ResponseFormatter::success($responseData, 'Model deleted successfully.');
        } catch (Exception $e) {
            Log::error('Error deleting model: ' . $e->getMessage());
            return ResponseFormatter::error('Failed to delete model: ' . $e->getMessage(), 500);
        }
    }

    public function recommend(Request $request, $userId)
    {
        $limit = $request->input('limit', 10);

        try {
            $responseData = $this->recommendationSystemRepository->recommendUser($userId, $limit);
            return ResponseFormatter::success($responseData, 'Recommendations retrieved successfully.');
        } catch (Exception $e) {
            Log::error('Error fetching recommendations: ' . $e->getMessage());
            return ResponseFormatter::error('Failed to fetch recommendations: ' . $e->getMessage(), 500);
        }
    }
}
