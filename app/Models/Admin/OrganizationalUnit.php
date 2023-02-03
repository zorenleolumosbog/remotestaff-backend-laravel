<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationalUnit extends Model
{
    //Disable timetamps default fields of update_at and create_at 
	public $timestamps = false;

    //Rename table user schema
	protected $table = 'tblm_organizational_unit';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ou_name',
        'registered_legal_name',
        'address_line1',
        'address_line2',
        'town_city',
        'is_hq',
        'createdby',
        'datecreated',
        'modifiedby',
        'datemodified'
    ];

    /**
     * Get the towncity for the organizational unit.
     */
    public function towncity()
    {
        return $this->hasOne(Towncity::class, 'id', 'town_city');
    }
}
