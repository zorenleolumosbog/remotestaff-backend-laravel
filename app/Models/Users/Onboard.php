<?php

namespace App\Models\Users;

use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Onboard extends Authenticatable implements JWTSubject,MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

	//Disable timetamps default fields of update_at and create_at
	public $timestamps = false;

	//rename table user schema
	protected $table = 'tblm_a_onboard_prereg';
    protected $primaryKey = 'id';


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'link_social_media_id',
        'password',
		'email',
		'email_verified_at',
		'email_passwd_conf',
		'ip_addr',
		'date_submitted',
		'is_verified',
        'is_social_media',
		'date_verified',
		'maxdays_rule_id',
		'maxdays_unverifed',
		'is_expired',
        'date_expired'
    ];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'email_passwd_conf'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
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

    /**
     * Get the skill level for the country.
     */
    public function basicInfo()
    {
        return $this->hasMany(OnboardProfileBasicInfo::class, 'reg_link_preregid');
    }

    public function basicInfo2()
    {
        return $this->hasOne(OnboardProfileBasicInfo::class, 'reg_link_preregid');
    }


}
