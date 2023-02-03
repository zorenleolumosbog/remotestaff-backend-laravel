<?php

namespace App\Models\Client;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientScreenCaptureDtl extends Model
{
    use HasFactory;

	//Disable timetamps default fields of update_at and create_at 
	public $timestamps = false;
    
    //Rename table user schema
	protected $table = 'tblt_screencap_dtl';
    protected $connection = 'mysql3';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'screen_name',
        'createdby',
        'datecreated',
        'modifiedby',
        'datemodified',
        'link_screencap_hdr_id',
        'timesheet_dtl_id_id',
        'photo'
    ];

}
