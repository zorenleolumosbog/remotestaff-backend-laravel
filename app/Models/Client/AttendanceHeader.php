<?php

namespace App\Models\Client;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceHeader extends Model
{
    use HasFactory;

	//Disable timetamps default fields of update_at and create_at
	public $timestamps = false;

    protected $connection = 'mysql3';

    //Rename table user schema
	protected $table = 'tblt_attendance_hdr';
}
