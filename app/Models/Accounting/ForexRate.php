<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForexRate extends Model
{
    use HasFactory;
    
	//Disable timetamps default fields of update_at and create_at 
	public $timestamps = false;

    protected $connection = 'mysql2';
    
    //Rename table user schema
	protected $table = 'tblm_forex_for_client_invoice';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'rate',
        'effective_month_year',
        'currency_id',
        'forex_rate_type_id',
        'isActive',
        'isEdited',
        'createdby',
        'datecreated',
        'modifiedby',
        'datemodified'
    ];
}
