<?php

namespace App\Models\Timesheet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimesheetDtl extends Model
{
    use HasFactory;

	//Disable timetamps default fields of update_at and create_at 
	public $timestamps = false;

    protected $connection = 'mysql2';
    
    //Rename table user schema
	protected $table = 'tblt_timesheet_dtl';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'link_tms_hdr',
        'date_worked',
        'work_time_in',
        'work_time_out',
        'work_total_hours',
        'reg_ros_hours',
        'start_lunch',
        'finish_lunch',
        'lunch_total_hours',
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
