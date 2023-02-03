<?php

namespace App\Models\Users;

use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class OnboardProfileContactInfo extends Authenticatable implements JWTSubject,MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;
	
	//Disable timetamps default fields of update_at and create_at 
	public $timestamps = false;
	
	//rename table user schema
	protected $table = 'tblm_d_onboard_contact_info';

    protected $casts = [
                'reg_birthdate'  => 'datetime:Y-m-d\TH:i'
        ];
	
    protected $primaryKey = 'ci_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ci_alt_email',
		'ci_mobile',
		'ci_landline',
        'ci_line1',
        'ci_line2',
        'ci_state',
        'ci_addr_state',
        'ci_addr_country',
        'ci_region',
        'ci_country',
        'ci_skypeid',
        'ci_fbid',
        'ci_linkedinid',
        'ci_referredby',
        'ci_referredmobile',
        'ci_referredemail',
        'ci_link_regid',
        'ci_prov_addr_state_province',
        'ci_addr_province_state',
        'ci_prov_addr_country',
        'ci_prov_addr_region',
        'ci_zipcode',
        'ci_alt_email2',
        'ci_alt_email3',
        'ci_primarymobile',
        'ci_secondarymobile',
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
	
	
}
