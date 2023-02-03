<?php

namespace App\Models\Admin;

use App\Models\Users\Onboard;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientRemoteContractor extends Model
{
    use HasFactory;

    //Disable timetamps default fields of update_at and create_at
	public $timestamps = false;

    //Rename table user schema
	protected $table = 'tblm_client_sub_contractor';

    protected $connection = 'mysql';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'reg_link_preregid',
        'actreg_contractor_id',
        'subcon_legacy_id',
        'client_sched_id',
        'subcon_sched_id',
        'is_contracted',
        'date_contracted',
        'is_contract_end',
        'date_contract_end',
        'agent_id',
        'userid',
        'staff_email',
        'initial_email_password',
        'skype_id',
        'initial_skype_password',
        'posting_id',
        'client_price',
        'client_price_effective_date',
        'payment_type',
        'php_monthly',
        'php_hourly',
        'overtime',
        'overtime_monthly_limit',
        'overtime_weekly_limit',
        'work_status',
        'work_days',
        'starting_date',
        'end_date',
        'status',
        'resignation_date',
        'current_rate',
        'client_timezone',
        'currency',
        'with_tax',
        'with_bp_comm',
        'with_aff_comm',
        'staff_currency_id',
        'staff_working_timezone',
        'job_designation',
        'contract_updated',
        'reason',
        'reason_type',
        'replacement_request',
        'date_terminated',
        'flexi',
        'prepaid',
        'prepaid_start_date',
        'staff_other_client_email',
        'staff_other_client_email_password',
        'service_type',
        'client_start_work_hour',
        'client_finish_work_hour',
        'service_agreement_id',
        'quote_details_id',
        'package_id',
        'staff_type_id',
        'account_status',
        'extra_hours',
        'createdby',
        'datecreated',
        'modifiedby',
        'datemodified'
    ];

    public function clients()
    {
        return $this->hasMany(ClientRemoteContractorPersonnel::class, 'link_subcon_id');
    }

    
    public function subcon_sched()
    {
        return $this->hasOne(SubcontractorSched::class, 'id' , 'subcon_sched_id');
    }
    public function client_sched()
    {
        return $this->hasOne(ClientSched::class, 'id', 'client_sched_id');
    }
}
