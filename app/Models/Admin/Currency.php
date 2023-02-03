<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory;

    //Disable timetamps default fields of update_at and create_at 
	public $timestamps = false;

    //Rename table user schema
	protected $table = 'tblm_currency';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'link_country_id',
        'code',
        'symbol',
        'description',
        'rate',
        'createdby',
        'datecreated',
        'modifiedby',
        'datemodified'
    ];

    /**
     * Get the rates for the currency.
     */
    public function taxRates()
    {
        return $this->hasMany(TaxRate::class, 'link_currency_id');
    }

    /**
     * Get the country that owns the currency.
     */
    public function country()
    {
        return $this->belongsTo(Country::class, 'link_country_id');
    }
}
