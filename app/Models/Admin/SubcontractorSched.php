<?php

namespace App\Models\Admin;

use App\Models\Users\OnboardProfileBasicInfo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubcontractorSched extends Model
{
    use HasFactory;

    //Disable timetamps default fields of update_at and create_at
	public $timestamps = false;

    //Rename table user schema
	protected $table = 'tblm_subcon_sched';

    protected $connection = 'mysql';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'working_hours',
        'working_days',
        'mon_start',
        'mon_finish',
        'mon_number_hrs',
        'mon_start_lunch',
        'mon_finish_lunch',
        'mon_lunch_number_hrs',
        'tue_start',
        'tue_finish',
        'tue_number_hrs',
        'tue_start_lunch',
        'tue_finish_lunch',
        'tue_lunch_number_hrs',
        'wed_start',
        'wed_finish',
        'wed_number_hrs',
        'wed_start_lunch',
        'wed_finish_lunch',
        'wed_lunch_number_hrs',
        'thu_start',
        'thu_finish',
        'thu_number_hrs',
        'thu_start_lunch',
        'thu_finish_lunch',
        'thu_lunch_number_hrs',
        'fri_start',
        'fri_finish',
        'fri_number_hrs',
        'fri_start_lunch',
        'fri_finish_lunch',
        'fri_lunch_number_hrs',
        'sat_start',
        'sat_finish',
        'sat_number_hrs',
        'sat_start_lunch',
        'sat_finish_lunch',
        'sat_lunch_number_hrs',
        'sun_start',
        'sun_finish',
        'sun_number_hrs',
        'sun_start_lunch',
        'sun_finish_lunch',
        'sun_lunch_number_hrs',
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
