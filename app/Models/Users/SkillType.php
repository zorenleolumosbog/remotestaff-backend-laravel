<?php

namespace App\Models\Users;

use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class SkillType extends Authenticatable 
{
	
	//Disable timetamps default fields of update_at and create_at 
	public $timestamps = false;
	
	//rename table user schema
	protected $table = 'tblm_skills';

	
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'desc',
		'createdby',
		'datecreated',
        'modifiedby',
		'datemodified'
    ];
    


    public function skillLevel()
    {
        return $this->belongsTo(SkillLevel::class, 'link_skill_id');
    }
    
	
	
}
