<?php

namespace App\Models\Client;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientScreenCaptureHdr extends Model
{
    use HasFactory;

	//Disable timetamps default fields of update_at and create_at 
	public $timestamps = false;
    
    //Rename table user schema
	protected $table = 'tblt_screencap_hdr';
    protected $connection = 'mysql3';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'link_subcon_id',
        'link_client_id',
        'status_id',
        'screen_count',
        'createdby',
        'datecreated',
        'modifiedby',
        'datemodified'
    ];

}
