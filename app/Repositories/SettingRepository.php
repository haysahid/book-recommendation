<?php

namespace App\Repositories;

use App\Models\Setting;
use Illuminate\Support\Facades\Log;

class SettingRepository
{
    private static function arrayToObject($value)
    {
        if (!is_array($value)) {
            return $value;
        }

        $isAssoc = array_keys($value) !== range(0, count($value) - 1);
        if ($isAssoc) {
            $obj = new \stdClass();
            foreach ($value as $k => $v) {
                $obj->{$k} = self::arrayToObject($v);
            }
            return $obj;
        }

        // Sequential array: convert elements recursively but keep as array
        return array_map([self::class, 'arrayToObject'], $value);
    }

    private static function processSettingsItems($items)
    {
        $result = [];
        foreach ($items as $key => $value) {
            $setting = Setting::where('key', $key)->first();
            if ($setting && $setting->type === 'json') {
                $value = json_decode($value, true);
            }
            if ($setting && $setting->type === 'boolean') {
                $value = $value === '1' ? true : false;
            }
            if (strpos($key, '.') !== false) {
                \Illuminate\Support\Arr::set($result, $key, $value);
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    public static function getSettings()
    {
        $items = Setting::all()->pluck('value', 'key')->toArray();
        $result = self::processSettingsItems($items);
        return self::arrayToObject($result);
    }

    public static function getSettingsDetailed()
    {
        $settings = Setting::all();
        $result = [];
        foreach ($settings as $setting) {
            if (strpos($setting->key, '.') !== false) {
                \Illuminate\Support\Arr::set($result, $setting->key, $setting->value);
            } else {
                $result[$setting->key] = $setting->value;
            }
        }
        return $result;
    }

    public static function updateOrCreateSetting($setting)
    {

        $existing = Setting::where('key', $setting['key'])->first();
        if ($existing) {
            Log::info('Updating setting: ' . json_encode($setting));
            $existing->value = $setting['value'];

            foreach (['type', 'name', 'description', 'group'] as $attr) {
                if (isset($setting[$attr])) {
                    $existing->$attr = $setting[$attr];
                }
            }

            Log::info('Setting exists. Updating value to: ' . $setting['value']);

            $existing->save();
            return $existing;
        } else {
            Log::info('Creating new setting: ' . json_encode($setting));
            return Setting::create($setting);
        }
    }

    /**
     * Retrieve a setting by its key.
     *
     * @param string $key The key of the setting to retrieve.
     * @return Setting|null The Setting model instance if found, or null if not found.
     */
    public static function getSettingByKey($key): ?Setting
    {
        return Setting::where('key', $key)->first();
    }

    /**
     * Retrieve settings whose keys start with the given prefix.
     *
     * @param string $prefix The prefix to filter setting keys.
     * @return object An object containing the processed settings with keys and values.
     */
    public static function getSettingByPrefix($prefix): object
    {
        $settings = Setting::where('key', 'like', $prefix . '%')->get()->pluck('value', 'key')->toArray();

        if (empty($settings)) {
            return self::arrayToObject([]); // Return empty object if no settings found
        }

        $result = self::processSettingsItems($settings);
        return self::arrayToObject($result)->model;
    }

    /**
     * Retrieve settings by group.
     *
     * @param string $group The group name to filter settings.
     * @return object An object containing the processed settings with keys and values.
     */
    public static function getSettingByGroup($group): object
    {
        $settings = Setting::where('group', $group)->get()->pluck('value', 'key')->toArray();

        if (empty($settings)) {
            return self::arrayToObject([]); // Return empty object if no settings found
        }

        $result = self::processSettingsItems($settings);
        return self::arrayToObject($result)->model;
    }
}
