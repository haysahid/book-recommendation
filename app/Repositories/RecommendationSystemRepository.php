<?php

namespace App\Repositories;

use App\Exports\BooksExport;
use App\Exports\TransactionItemsExport;
use GuzzleHttp\Client;
use Maatwebsite\Excel\Excel as MaatwebsiteExcel;
use Maatwebsite\Excel\Facades\Excel;

class RecommendationSystemRepository
{
    protected Client $client;
    protected string $baseUrl;

    protected BooksExport $booksExport;
    protected TransactionItemsExport $transactionItemsExport;

    public function __construct()
    {
        $this->baseUrl = env('RECOMMENDATION_SYSTEM_API_URL');
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
        ]);

        $this->booksExport = new BooksExport();
        $this->transactionItemsExport = new TransactionItemsExport();
    }

    private function convertToIso8601WithTimezone($datetimeString)
    {
        $dt = new \DateTime($datetimeString, new \DateTimeZone('UTC'));
        return $dt->format('c'); // ISO 8601 with timezone
    }

    private function prepareMultipartFile(
        $name,
        $file,
        $exportInstance,
        $exportPath,
        $exportFilename
    ) {
        if (!$file) {
            $tempFilePath = storage_path('app/public/' . $exportPath);
            Excel::store(
                export: $exportInstance,
                filePath: $exportPath,
                disk: 'public',
                writerType: MaatwebsiteExcel::XLSX
            );
            return [
                'name'     => $name,
                'contents' => fopen($tempFilePath, 'r'),
                'filename' => $exportFilename,
            ];
        }
        return [
            'name'     => $name,
            'contents' => fopen($file->getRealPath(), 'r'),
            'filename' => $file->getClientOriginalName(),
        ];
    }

    public function trainModel(
        $booksFile,
        $transactionsFile,
        $reference,
        $nFactors,
        $nEpochs,
        $lrAll,
        $regAll,
        $createdBy,
    ) {
        $multipart = [];

        // Prepare files
        $multipart[] = $this->prepareMultipartFile(
            name: 'books_file',
            file: $booksFile,
            exportInstance: $this->booksExport,
            exportPath: 'exports/books_export.xlsx',
            exportFilename: 'books_export.xlsx'
        );
        $multipart[] = $this->prepareMultipartFile(
            name: 'transactions_file',
            file: $transactionsFile,
            exportInstance: $this->transactionItemsExport,
            exportPath: 'exports/transactions_export.xlsx',
            exportFilename: 'transactions_export.xlsx'
        );

        // Add parameters if set
        foreach (
            [
                'reference' => $reference,
                'n_factors' => $nFactors,
                'n_epochs'  => $nEpochs,
                'lr_all'    => $lrAll,
                'reg_all'   => $regAll,
                'created_by' => $createdBy,
            ] as $key => $value
        ) {
            if (isset($value)) {
                $multipart[] = [
                    'name'     => $key,
                    'contents' => $value,
                ];
            }
        }

        $response = $this->client->post('/train', [
            'multipart' => $multipart,
        ]);

        $result = json_decode($response->getBody()->getContents(), true);

        // Convert 'created_at' to ISO 8601 with timezone if present
        if (isset($result['model']['created_at'])) {
            $result['model']['created_at'] = $this->convertToIso8601WithTimezone($result['model']['created_at']);
        }

        return $result;
    }

    public function tuneModel(
        $booksFile,
        $transactionsFile,
        $reference,
        $paramGrid,
        $cv,
        $nJobs,
    ) {
        $multipart = [];

        // Prepare files
        $multipart[] = $this->prepareMultipartFile(
            name: 'books_file',
            file: $booksFile,
            exportInstance: $this->booksExport,
            exportPath: 'exports/books_export.xlsx',
            exportFilename: 'books_export.xlsx'
        );
        $multipart[] = $this->prepareMultipartFile(
            name: 'transactions_file',
            file: $transactionsFile,
            exportInstance: $this->transactionItemsExport,
            exportPath: 'exports/transactions_export.xlsx',
            exportFilename: 'transactions_export.xlsx'
        );

        // Add other parameters
        foreach (
            [
                'reference'   => $reference ?? null,
                'param_grid'  => isset($paramGrid) ? json_encode($paramGrid) : null,
                'cv'          => $cv ?? null,
                'n_jobs'      => $nJobs ?? null,
            ] as $key => $value
        ) {
            if (isset($value)) {
                $multipart[] = [
                    'name'     => $key,
                    'contents' => $value,
                ];
            }
        }

        $response = $this->client->post('/tune', [
            'multipart' => $multipart,
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    public function getModelHistory()
    {
        $response = $this->client->get('/model-history');
        $result = json_decode($response->getBody()->getContents(), true);

        // Convert 'created_at' to ISO 8601 with timezone for each model
        if (is_array($result['models'] ?? null)) {
            foreach ($result['models'] as &$model) {
                if (isset($model['created_at'])) {
                    $model['created_at'] = $this->convertToIso8601WithTimezone($model['created_at']);
                }
            }
        }

        return $result;
    }

    public function setActiveModel($modelId)
    {
        $response = $this->client->post("/set-active-model/{$modelId}");
        $result = json_decode($response->getBody()->getContents(), true);

        if (isset($result['created_at'])) {
            $result['created_at'] = $this->convertToIso8601WithTimezone($result['created_at']);
        } elseif (isset($result['model']['created_at'])) {
            $result['model']['created_at'] = $this->convertToIso8601WithTimezone($result['model']['created_at']);
        }

        return $result;
    }

    public function getActiveModel()
    {
        $response = $this->client->get('/active-model');
        $result = json_decode($response->getBody()->getContents(), true);

        if (isset($result['created_at'])) {
            $result['created_at'] = $this->convertToIso8601WithTimezone($result['created_at']);
        } elseif (isset($result['model']['created_at'])) {
            $result['model']['created_at'] = $this->convertToIso8601WithTimezone($result['model']['created_at']);
        }

        return $result;
    }

    public function deleteModel($modelId)
    {
        $response = $this->client->delete("/model-history/{$modelId}");
        return json_decode($response->getBody()->getContents(), true);
    }

    public function recommendUser($userId, $limit)
    {
        $response = $this->client->get("/recommend/{$userId}", [
            'query' => [
                'limit' => $limit,
            ],
        ]);
        return json_decode($response->getBody()->getContents(), true);
    }
}
