<?php

namespace App\Models\Timesheet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimesheetHdr extends Model
{
    use HasFactory;

	//Disable timetamps default fields of update_at and create_at 
	public $timestamps = false;

    protected $connection = 'mysql2';
    
    //Rename table user schema
	protected $table = 'tblt_timesheet_hdr';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'legacy_timesheet_id',
        'link_subcon_id',
        'link_client_id',
        'status_id',
        'work_total_hours',
        'month_year',
        'timezone_id',
        'createdby',
        'datecreated',
        'modifiedby',
        'datemodified'
    ];

    public function dtl()
    {
        return $this->hasMany(TimesheetAdjDtl::class, 'link_adj_hdr_id');
    }
}
