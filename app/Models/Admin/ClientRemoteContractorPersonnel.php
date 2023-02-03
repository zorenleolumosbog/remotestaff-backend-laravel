<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientRemoteContractorPersonnel extends Model
{
    use HasFactory;

    //Disable timetamps default fields of update_at and create_at
	public $timestamps = false;

    //Rename table user schema
	protected $table = 'tblm_client_subcon_pers';

    protected $connection = 'mysql2';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'link_subcon_id',
        'link_client_id',
        'createdby',
        'datecreated',
        'modifiedby',
        'datemodified'
    ];

    public function basicInfo()
    {
        return $this->belongsTo(Client::class, 'link_client_id');
    }
}
