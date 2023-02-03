<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientSubcontractors extends Model
{
    use HasFactory;

	//Disable timetamps default fields of update_at and create_at 
	public $timestamps = false;

    protected $connection = 'mysql2';
    
    //Rename table user schema
	protected $table = 'tblm_client_subcon_pers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'link_subcon_id',
        'link_client_id',
        'createdby',
        'datecreated',
        'modifiedby',
        'datemodified'
    ];
}
