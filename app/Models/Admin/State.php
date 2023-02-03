<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    use HasFactory;

    //Disable timetamps default fields of update_at and create_at 
	public $timestamps = false;

    //Rename table user schema
	protected $table = 'tblm_state';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'link_country_id',
        'link_region_id',
        'description',
        'createdby',
        'datecreated',
        'modifiedby',
        'datemodified'
    ];

    /**
     * Get the town cities for the state.
     */
    public function towncities()
    {
        return $this->hasMany(Towncity::class, 'link_state_id');
    }

    /**
     * Get the country that owns the state.
     */
    public function country()
    {
        return $this->belongsTo(Country::class, 'link_country_id');
    }

    /**
     * Get the region that owns the sate.
     */
    public function region()
    {
        return $this->belongsTo(Region::class, 'link_region_id');
    }
}
