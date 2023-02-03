<?php

namespace App\Models\Client;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientReport extends Model
{
    use HasFactory;

	//Disable timetamps default fields of update_at and create_at 
	public $timestamps = false;
    
    //Rename table user schema
	protected $table = 'legacy_screen_capture';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        '_id',
        'type',
        'datetime',
        'userid',
        'userid_email',
        'screen_count',
        'fs',
        'image_db_name',
        'timerecord_id',
        'leads_id',
        'activity_note',
        'activity_note_requested',
        'activity_note_responded',
        'activity_note_status',
        'subcontract_id'
    ];

}
