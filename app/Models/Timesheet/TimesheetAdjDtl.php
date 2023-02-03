<?php

namespace App\Models\Timesheet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimesheetAdjDtl extends Model
{
    use HasFactory;

	//Disable timetamps default fields of update_at and create_at 
	public $timestamps = false;

    protected $connection = 'mysql2';
    
    //Rename table user schema
	protected $table = 'tblt_timesheet_adj_dtl';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'link_adj_hdr_id',
        'link_timesheet_dtl_id',
        'date',
        'adjusted_hours',
        'createdby',
        'datecreated',
        'modifiedby',
        'datemodified'
    ];

    
    
}
