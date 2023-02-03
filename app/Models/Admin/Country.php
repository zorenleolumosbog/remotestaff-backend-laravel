<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;
    //TODO: Uncomment for hasManyDeep relationship
    //composer require staudenmeir/eloquent-has-many-deep:"^1.7"
    // use \Staudenmeir\EloquentHasManyDeep\HasRelationships;

    //Disable timetamps default fields of update_at and create_at 
	public $timestamps = false;

    //Rename table user schema
	protected $table = 'tblm_country';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'short_desc',
        'long_desc',
        'with_region',
        'createdby',
        'datecreated',
        'modifiedby',
        'datemodified'
    ];

    /**
     * Get the regions for the country.
     */
    public function regions()
    {
        return $this->hasMany(Region::class, 'link_country_id');
    }

    /**
     * Get the states for the country.
     */
    public function states()
    {
        return $this->hasMany(State::class, 'link_country_id');
    }

    /**
     * Get all of the towncities for the country.
     */
    public function towncities()
    {
        return $this->hasManyThrough(
            Towncity::class, 
            State::class,
            'link_country_id',
            'link_state_id',
        );
    }

    /**
     * Get all of the barangays for the country.
     */
    //TODO: Uncomment for hasManyDeep relationship
    //composer require staudenmeir/eloquent-has-many-deep:"^1.7"
    // public function barangays() {
    //     return $this->hasManyDeep(
    //         Barangay::class, [
    //             Region::class, 
    //             State::class, 
    //             Towncity::class
    //         ],
    //         [
    //             'link_country_id',
    //             'link_region_id',
    //             'link_state_id',
    //             'link_towncity_id'
    //         ]
    //     );
    // }

    /**
     * Get the currency for the country.
     */
    public function currency()
    {
        return $this->hasOne(Currency::class, 'link_country_id');
    }
}
