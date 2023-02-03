<?php

namespace App\Models\Admin;

use App\Models\Users\OnboardProfileBasicInfo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientSched extends Model
{
    use HasFactory;

    //Disable timetamps default fields of update_at and create_at
	public $timestamps = false;

    //Rename table user schema
	protected $table = 'tblm_client_sched';

    protected $connection = 'mysql';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'client_start_work_hour',
        'client_finish_work_hour',
        'createdby',
        'datecreated',
        'modifiedby',
        'datemodified',
    ];

    public function subcontractor()
    {
        return $this->belongsTo(ClientRemoteContractor::class, 'id');
    }
}
