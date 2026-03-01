<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Customer extends Model
{
    use LogsActivity;
    protected $fillable = [
        'name',
        'code',
        'phone',
        'phone_business',
        'handphone',
        'whatsapp',
        'email',
        'fax',
        'website',
        'address',
        'billing_street',
        'billing_city',
        'billing_postcode',
        'billing_province',
        'billing_country',
        'group',
        'category',
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
