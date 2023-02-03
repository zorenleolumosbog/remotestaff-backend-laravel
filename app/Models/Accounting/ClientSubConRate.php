<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientSubConRate extends Model
{
    use HasFactory;
    
	//Disable timetamps default fields of update_at and create_at 
	public $timestamps = false;

    protected $connection = 'mysql2';
    
    //Rename table user schema
	protected $table = 'tblm_client_subcon_rate';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'link_client_subcon_pers',
        'basic_monthly_rate',
        'basic_weekly_rate',
        'basic_daily_rate',
        'basic_hourly_rate',
        'effective_date_from',
        'effective_date_to',
        'is_active',
        'createdby',
        'datecreated',
        'modifiedby',
        'datemodified'
    ];
}
