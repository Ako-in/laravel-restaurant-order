<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesTarget extends Model
{
    use HasFactory;

    protected $table = 'sales_targets';

    protected $fillable = [
        'start_date',
        'end_date',
        'period_type',
        'target_amount',
    ];
}
