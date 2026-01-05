<?php

namespace App\Http\Controllers\Admin\API;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Repositories\SettingRepository;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function setAutoTrainingModel(Request $request)
    {
        $validatedData = $request->validate([
            'auto_training' => 'required|boolean',
            'reference' => 'nullable|string',
            'n_factors' => 'nullable|integer',
            'n_epochs' => 'nullable|integer',
            'lr_all' => 'nullable|numeric',
            'reg_all' => 'nullable|numeric',
        ]);

        $group = 'model';

        $autoTraining = new Setting();
        $autoTraining->key = 'auto_training';
        $autoTraining->value = $validatedData['auto_training'] ? '1' : '0';
        $autoTraining->type = 'boolean';
        $autoTraining->group = $group;

        $autoTraining = SettingRepository::updateOrCreateSetting($autoTraining);

        if (isset($validatedData['reference'])) {
            $reference = new Setting();
            $reference->key = 'reference';
            $reference->value = $validatedData['reference'];
            $reference->type = 'text';
            $reference->group = $group;

            SettingRepository::updateOrCreateSetting($reference);
        }

        if (isset($validatedData['n_factors'])) {
            $nFactors = new Setting();
            $nFactors->key = 'n_factors';
            $nFactors->value = (string)$validatedData['n_factors'];
            $nFactors->type = 'integer';
            $nFactors->group = $group;

            SettingRepository::updateOrCreateSetting($nFactors);
        }

        if (isset($validatedData['n_epochs'])) {
            $nEpochs = new Setting();
            $nEpochs->key = 'n_epochs';
            $nEpochs->value = (string)$validatedData['n_epochs'];
            $nEpochs->type = 'integer';
            $nEpochs->group = $group;

            SettingRepository::updateOrCreateSetting($nEpochs);
        }

        if (isset($validatedData['lr_all'])) {
            $lrAll = new Setting();
            $lrAll->key = 'lr_all';
            $lrAll->value = (string)$validatedData['lr_all'];
            $lrAll->type = 'float';
            $lrAll->group = $group;

            SettingRepository::updateOrCreateSetting($lrAll);
        }

        if (isset($validatedData['reg_all'])) {
            $regAll = new Setting();
            $regAll->key = 'reg_all';
            $regAll->value = (string)$validatedData['reg_all'];
            $regAll->type = 'float';
            $regAll->group = $group;

            SettingRepository::updateOrCreateSetting($regAll);
        }

        return response()->json(['message' => 'Auto training model setting updated successfully.']);
    }
}
