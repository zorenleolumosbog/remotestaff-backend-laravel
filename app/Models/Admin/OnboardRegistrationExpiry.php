<?php

namespace App\Models\Admin;

use App\Models\Users\OnboardProfileBasicInfo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnboardRegistrationExpiry extends Model
{
    use HasFactory;

    //Disable timetamps default fields of update_at and create_at 
	public $timestamps = false;

    //Rename table user schema
	protected $table = 'tblm_onboard_registration_expiry';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'link_preregid',
        'description',
        'createdby',
        'datecreated',
        'modifiedby',
        'datemodified'
    ];

    /**
     * Get the onboard registrant that owns the onboard registration expiry.
     */
    public function onboard()
    {
        return $this->belongsTo(OnboardRegistrant::class, 'link_preregid');
    }

    /**
     * Get the onboard registrant basic info that owns the onboard registration expiry.
     */
    public function onboardBasicInfo()
    {
        return $this->belongsTo(OnboardProfileBasicInfo::class, 'link_preregid', 'reg_link_preregid');
    }
}
