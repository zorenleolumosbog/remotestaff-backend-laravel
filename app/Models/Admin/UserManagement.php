<?php

namespace App\Models\Admin;

use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class UserManagement extends Authenticatable implements JWTSubject, MustVerifyEmail
{
    use HasFactory, Notifiable;
	
	//Disable timetamps default fields of update_at and create_at 
	public $timestamps = false;
	
	//Rename table user schema
	protected $table = 'tblm_admin_user';
	
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'link_admin_user_role_id',
		'email',
        'password',
        'firstname',
        'middlename',
        'lastname',
		'is_verified',
		'dateverified',
		'createdby',
        'datecreated',
        'modifiedby',
        'datemodified'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password'
    ];

    /**
     * Get the user type associated with the admin user.
     */
    public function userRole()
    {
        return $this->hasOne(UserRole::class, 'id', 'link_admin_user_role_id');
    }

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
