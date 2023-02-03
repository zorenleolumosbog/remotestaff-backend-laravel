<?php

namespace App\Models\Client;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimesheetDetail extends Model
{
    use HasFactory;

	//Disable timetamps default fields of update_at and create_at
	public $timestamps = false;

    protected $connection = 'mysql2';

    //Rename table user schema
	protected $table = 'tblt_timesheet_dtl';
}
