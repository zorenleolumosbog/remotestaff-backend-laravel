<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepartmentSection extends Model
{
    use HasFactory;

    //Disable timetamps default fields of update_at and create_at
	public $timestamps = false;

    //Rename table user schema
	protected $table = 'tblo_dept_sec';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'link_dept_id',
        'description',
        'createdby',
        'datecreated',
        'modifiedby',
        'datemodified'
    ];

    /**
     * Get the personnel for the sections.
     */
    public function personnel()
    {
        return $this->hasMany(DepartmentSectionPersonnel::class, 'link_sec_id');
    }

    /**
     * Get the department for the sections.
     */
    public function department()
    {
        return $this->belongsTo(Department::class, 'link_dept_id');
    }
}
