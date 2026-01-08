<?php

namespace App\UseCases;

use App\Jobs\TrainModel;
use App\Repositories\SettingRepository;
use Illuminate\Support\Facades\Log;

class AutoTrainModelUseCase
{
    private SettingRepository $settingRepository;

    public function __construct()
    {
        $this->settingRepository = new SettingRepository();
    }

    public function execute(): void
    {
        $modelSetting = $this->settingRepository->getSettingByPrefix('model.');

        Log::info('AutoTrainModelUseCase: Retrieved model settings', ['settings' => $modelSetting]);

        // Debounce: Only allow training if last training was more than interval ago
        $debounceKey = 'auto_train_model_last_run';
        $interval = (float)$modelSetting->auto_training_interval;
        $intervalUnit = $modelSetting->interval_unit ?? 'seconds';

        if ($intervalUnit === 'minutes') {
            $interval *= 60;
        } elseif ($intervalUnit === 'hours') {
            $interval *= 3600;
        } elseif ($intervalUnit === 'days') {
            $interval *= 86400;
        } else {
            // Default to seconds if unit is unrecognized
            $interval *= 1;
        }

        $lastRun = cache()->get($debounceKey);

        if ($modelSetting->auto_training) {
            if (!$lastRun || now()->diffInSeconds($lastRun) > $interval) {
                Log::info('AutoTrainModelUseCase: Dispatching TrainModel job');
                TrainModel::dispatch([
                    'reference' => $modelSetting->reference,
                    'n_factors' => (int)$modelSetting->n_factors,
                    'n_epochs' => (int)$modelSetting->n_epochs,
                    'lr_all' => (float)$modelSetting->lr_all,
                    'reg_all' => (float)$modelSetting->reg_all,
                    'created_by' => 'auto',
                ]);
                cache()->put($debounceKey, now(), $interval);
            } else {
                Log::info('AutoTrainModelUseCase: Debounced, skipping training');
            }
        }
    }
}
