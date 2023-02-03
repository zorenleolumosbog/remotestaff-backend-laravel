<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegacyPersonal extends Model
{
    use HasFactory;

    //Disable timetamps default fields of update_at and create_at
	public $timestamps = false;

    //Rename table user schema
	protected $table = 'personal';

    public function subcontractors()
    {
        return $this->hasMany(LegacySubcontractor::class, 'userid', 'userid');
    }
}
