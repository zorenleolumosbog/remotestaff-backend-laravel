<?php

namespace App\Models\SecurityAccessMatrix;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pillar extends Model
{
    use HasFactory;

    //Disable timetamps default fields of update_at and create_at 
	public $timestamps = false;

    //Rename table user schema
	protected $table = 'tbls_pillar';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'description',
        'createdby',
        'datecreated',
        'modifiedby',
        'datemodified',
        'isactive'
    ];

    /**
     * Get the sub pillars.
     */
    public function sub_pillars()
    {
        return $this->hasMany(SubPillar::class, 'link_pillar_id')->where('isactive','=', 1);;
    }

    
}
