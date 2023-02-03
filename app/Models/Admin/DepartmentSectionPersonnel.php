<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepartmentSectionPersonnel extends Model
{
    use HasFactory;

    //Disable timetamps default fields of update_at and create_at
	public $timestamps = false;

    //Rename table user schema
	protected $table = 'tblo_dept_sec_pers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'link_sec_id',
        'link_prereg_id',
        'createdby',
        'datecreated',
        'modifiedby',
        'datemodified'
    ];

    /**
     * Get the section for the personnel.
     */
    public function section()
    {
        return $this->belongsTo(DepartmentSection::class, 'link_sec_id');
    }

    /**
     * Get the registrant for the personnel.
     */
    public function registrant()
    {
        return $this->belongsTo(Registrant::class, 'link_prereg_id');
    }
}
