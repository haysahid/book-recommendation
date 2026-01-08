<?php

namespace App\Jobs;

use App\Repositories\RecommendationSystemRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class TrainModel implements ShouldQueue
{
    use Queueable, Dispatchable;

    protected $reference;
    protected $nFactors;
    protected $nEpochs;
    protected $lrAll;
    protected $regAll;
    protected $createdBy;

    /**
     * Create a new job instance.
     */
    public function __construct(array $params)
    {
        $this->reference = $params['reference'] ?? null;
        $this->nFactors = $params['n_factors'] ?? null;
        $this->nEpochs = $params['n_epochs'] ?? null;
        $this->lrAll = $params['lr_all'] ?? null;
        $this->regAll = $params['reg_all'] ?? null;
        $this->createdBy = $params['created_by'] ?? null;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Starting ' . $this->createdBy . ' model training job.');

        $recommendationSystemRepository = new RecommendationSystemRepository();
        $recommendationSystemRepository->trainModel(
            booksFile: null,
            transactionsFile: null,
            reference: $this->reference,
            nFactors: $this->nFactors,
            nEpochs: $this->nEpochs,
            lrAll: $this->lrAll,
            regAll: $this->regAll,
            createdBy: $this->createdBy,
        );
    }
}
