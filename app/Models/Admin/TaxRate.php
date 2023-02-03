<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxRate extends Model
{
    use HasFactory;

    //Disable timetamps default fields of update_at and create_at 
	public $timestamps = false;

    //Rename table user schema
	protected $table = 'tblm_tax_rate';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'link_country_id',
        'link_state_id',
        'link_tax_type_id',
        'state_applied',
        'description',
        'rate',
        'createdby',
        'datecreated',
        'modifiedby',
        'datemodified'
    ];

    /**
     * Get the country that owns the tax rate.
     */
    public function country()
    {
        return $this->belongsTo(Country::class, 'link_country_id');
    }

    /**
     * Get the state that owns the tax rate.
     */
    public function state()
    {
        return $this->belongsTo(State::class, 'link_state_id');
    }

    /**
     * Get the tax type that owns the tax rate.
     */
    public function taxType()
    {
        return $this->belongsTo(TaxType::class, 'link_tax_type_id');
    }
}
