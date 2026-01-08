<?php

namespace App\Http\Controllers\Admin\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Repositories\SettingRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SettingController extends Controller
{
    public function setAutoTrainingModel(Request $request)
    {
        $validatedData = $request->validate([
            'auto_training' => 'required|boolean',
            'auto_training_interval' => 'nullable|numeric',
            'interval_unit' => 'nullable|string|in:seconds,minutes,hours,days',
            'reference' => 'nullable|string',
            'n_factors' => 'nullable|integer',
            'n_epochs' => 'nullable|integer',
            'lr_all' => 'nullable|numeric',
            'reg_all' => 'nullable|numeric',
        ]);

        $keyPrefix = 'model.';
        $group = 'general';

        // Get previous interval to check for changes
        $prevInterval = Setting::where('key', $keyPrefix . 'auto_training_interval')->value('value');
        $newInterval = isset($validatedData['auto_training_interval']) ? (string)$validatedData['auto_training_interval'] : null;

        $settings = [
            [
                'key' => $keyPrefix . 'auto_training',
                'value' => $validatedData['auto_training'] ? '1' : '0',
                'type' => 'boolean',
            ],
            [
                'key' => $keyPrefix . 'auto_training_interval',
                'value' => $newInterval,
                'type' => 'float',
            ],
            [
                'key' => $keyPrefix . 'interval_unit',
                'value' => $validatedData['interval_unit'] ?? null,
                'type' => 'string',
            ],
            [
                'key' => $keyPrefix . 'reference',
                'value' => $validatedData['reference'] ?? null,
                'type' => 'string',
            ],
            [
                'key' => $keyPrefix . 'n_factors',
                'value' => isset($validatedData['n_factors']) ? (string)$validatedData['n_factors'] : null,
                'type' => 'integer',
            ],
            [
                'key' => $keyPrefix . 'n_epochs',
                'value' => isset($validatedData['n_epochs']) ? (string)$validatedData['n_epochs'] : null,
                'type' => 'integer',
            ],
            [
                'key' => $keyPrefix . 'lr_all',
                'value' => isset($validatedData['lr_all']) ? (string)$validatedData['lr_all'] : null,
                'type' => 'float',
            ],
            [
                'key' => $keyPrefix . 'reg_all',
                'value' => isset($validatedData['reg_all']) ? (string)$validatedData['reg_all'] : null,
                'type' => 'float',
            ],
        ];

        foreach ($settings as $setting) {
            if (!is_null($setting['value'])) {
                $setting['group'] = $group;
                SettingRepository::updateOrCreateSetting($setting);
            }
        }

        // If auto_training_interval changes, clear cache
        if ($prevInterval !== $newInterval) {
            Log::info('SettingController: Auto-training interval changed, clearing debounce cache');
            cache()->forget('auto_train_model_last_run');
        }

        return ResponseFormatter::success(
            $validatedData,
            'Auto-training model setting updated successfully.'
        );
    }
}
