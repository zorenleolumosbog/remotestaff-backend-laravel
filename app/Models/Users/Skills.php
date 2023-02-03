<?php

namespace App\Models\Users;

use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Skills extends Authenticatable 
{
   
	//Disable timetamps default fields of update_at and create_at 
	public $timestamps = false;
	
	//rename table user schema
	protected $table = 'tblm_g_onboard_skills';

	
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'link_regid',
		'link_skill_id',
		'link_level_id',
        'createdby',
		'datecreated',
        'modifiedby',
        'datemodified'
    ];
    
    /**
     * Get the skill level for the country.
     */
    public function skillLevel()
    {
        return $this->hasMany(SkillLevel::class, 'link_level_id');
    }

    /**
     * Get the skill level for the country.
     */
    public function skillType()
    {
        return $this->hasMany(SkillType::class, 'link_skill_id');
    }


     /**
     * Get the skill level for the country.
     */
    public function jobSeeker()
    {
        return $this->hasMany(Onboard::class, 'id');
    }


    



 
	
	
	
}
