<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Towncity extends Model
{
    use HasFactory;

    //Disable timetamps default fields of update_at and create_at 
	public $timestamps = false;

    //Rename table user schema
	protected $table = 'tblm_towncity';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'link_state_id',
        'zip_code',
        'description',
        'createdby',
        'datecreated',
        'modifiedby',
        'datemodified'
    ];

    /**
     * Get the barangays for the town city.
     */
    public function barangays()
    {
        return $this->hasMany(Barangay::class, 'link_towncity_id');
    }

    /**
     * Get the state that owns the towncity.
     */
    public function state()
    {
        return $this->belongsTo(State::class, 'link_state_id');
    }
}
