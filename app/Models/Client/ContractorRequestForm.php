<?php

namespace App\Models\client;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractorRequestForm extends Model
{
    use HasFactory;

	//Disable timetamps default fields of update_at and create_at 
	public $timestamps = false;
    
    //Rename table user schema
	protected $table = 'tblm_contractor_request_form';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'link_prereg_id',
        'bh_jo_id',
        'crf_title',
        'crf_no_staffs',
        'crf_description',
        'crf_timezone',
        'crf_ofshore',
        'crf_hourly_rate',
        'crf_expertise_level',
        'crf_adv_skills',
        'crf_mid_skills',
        'crf_expected_tof',
        'crf_role_obj',
        'crf_industry',
        'crf_os',
        'crf_required_tools',
        'crf_au_number',
        'crf_monitors',
        'crf_comm_tools',
        'crf_existing_team',
        'crf_company_age',
        'crf_no_employees',
        'crf_sourcing',
        'crf_team_size',
        'crf_job_type',
        'createdby',
        'datecreated',
        'modifiedby',
        'datemodified'
    ];
}
