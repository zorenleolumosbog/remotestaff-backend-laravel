<?php

namespace App\Models\Users;


use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class SkillLevel extends Authenticatable 
{
    use HasApiTokens, HasFactory, Notifiable;
	
	//Disable timetamps default fields of update_at and create_at 
	public $timestamps = false;
	
	//rename table user schema
	protected $table = 'tblm_skill_level';

	
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
    

    public function skills()
    {
        return $this->hasMany(Skills::class, 'link_skill_id');
    }
	
    public function skillType()
    {
        return $this->hasMany(SkillType::class, 'id');
    }


    public function scopeWithSkills($query)
    {
        return 
                    $query->with(['skills' => function ($query) {
                        $query->orderBy('id', 'desc')
                                ->get();
                    }]);
             
    }
	
	
}
