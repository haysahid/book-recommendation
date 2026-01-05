<?php

namespace App\Repositories;

use App\Models\Setting;

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

    public static function getSettings()
    {
        $items = Setting::all()->pluck('value', 'key')->toArray();
        $result = [];
        foreach ($items as $key => $value) {
            $setting = Setting::where('key', $key)->first();
            if ($setting && $setting->type === 'json') {
                $value = json_decode($value, true);
            }
            if (strpos($key, '.') !== false) {
                \Illuminate\Support\Arr::set($result, $key, $value);
            } else {
                $result[$key] = $value;
            }
        }
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

    public static function updateOrCreateSetting(Setting $setting)
    {
        $existing = Setting::where('key', $setting['key'])->first();
        if ($existing) {
            $existing->value = $setting->value;
            if (isset($setting->type)) {
                $existing->type = $setting->type;
            }
            if (isset($setting->name)) {
                $existing->name = $setting->name;
            }
            if (isset($setting->description)) {
                $existing->description = $setting->description;
            }
            if (isset($setting->group)) {
                $existing->group = $setting->group;
            }
            $existing->save();
        } else {
            Setting::create($setting);
        }
    }
}
