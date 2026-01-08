<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

enum SettingType: string
{
    case TEXT = 'text';
    case STRING = 'string';
    case INTEGER = 'integer';
    case FLOAT = 'float';
    case BOOLEAN = 'boolean';
    case JSON = 'json';
}

enum SettingGroup: string
{
    case GENERAL = 'general';
    case RECOMMENDATION_SYSTEM = 'recommendation_system';
}

enum SettingPrefix: string
{
    case SITE = 'site.';
    case COMPANY = 'company.';
    case APP = 'app.';
    case MODEL = 'model.';
}

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'name',
        'description',
        'group',
    ];
}
