<?php

namespace App\Models\Users;

use App\Models\Admin\ClientRemoteContractor;
use App\Models\Admin\DepartmentSectionPersonnel;
use App\Models\Admin\RegistrantType;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class OnboardProfileBasicInfo extends Authenticatable implements JWTSubject,MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

	//Disable timetamps default fields of update_at and create_at
	public $timestamps = false;

	//rename table user schema
	protected $table = 'tblm_b_onboard_actreg_basic';

    protected $connection = 'mysql';

    protected $casts = [
                'reg_birthdate'  => 'datetime:Y-m-d\TH:i'
        ];

    protected $primaryKey = 'reg_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'legacy_subcon_id',
        'legacy_user_id',
        'reg_link_preregid',
		'registrant_type',
        'reg_nickname',
		'reg_firstname',
		'reg_middlename',
		'reg_lastname',
        'reg_birthdate',
        'reg_civilstatus',
        'reg_religion',
        'reg_philhealthid',
        'reg_pagibigid',
        'reg_tin',
        'reg_sss_id',
        'reg_datecreated',
        'reg_datemodified',
        'reg_modifiedby',
        'reg_birthdate',
        'reg_gender',
		'reg_nationality',
        'reg_prefix',
        'reg_home_addr_line1',
        'reg_home_addr_line2',
        'reg_home_addr_towncity',
        'reg_prov_addr_line1',
        'reg_prov_addr_line2',
        'reg_prov_addr_towncity',
        'reg_source',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function onboard()
    {
        return $this->belongsTo(Onboard::class, 'reg_link_preregid');
    }

    public function registrantType()
    {
        return $this->belongsTo(RegistrantType::class, 'registrant_type');
    }

    public function contract()
    {
        return $this->hasOne(ClientRemoteContractor::class, 'reg_link_preregid', 'reg_link_preregid');
    }

    public function section()
    {
        return $this->belongsTo(DepartmentSectionPersonnel::class, 'reg_link_preregid', 'link_prereg_id');
    }

}
