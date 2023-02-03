<?php

namespace App\Models\Users;

use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class ContLandline extends Authenticatable implements JWTSubject,MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;
	
	//Disable timetamps default fields of update_at and create_at 
	public $timestamps = false;
	
	//rename table user schema
	protected $table = 'tblm_d_onboard_contact_landline';

	
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'link_regid',
		'landline_number',
		'is_primary',
        'createdby',
        'datecreated',
        'modifiedby',
        'datemodified',
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
