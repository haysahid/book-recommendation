<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Store extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'address',
        'email',
        'phone',
        'logo',
        'banner',
        'rajaongkir_origin_id',
        'rajaongkir_origin_label',
        'province_name',
        'city_name',
        'district_name',
        'subdistrict_name',
        'zip_code',
    ];
}
