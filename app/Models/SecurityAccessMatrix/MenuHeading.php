<?php

namespace App\Models\SecurityAccessMatrix;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuHeading extends Model
{
    use HasFactory;

    //Disable timetamps default fields of update_at and create_at 
	public $timestamps = false;

    //Rename table user schema
	protected $table = 'tbls_menu_heading';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'link_sub_pillar_id',
        'description',
        'createdby',
        'datecreated',
        'modifiedby',
        'datemodified',
        'isactive'
    ];

    /**
     * Get the Pillar for the invoice header.
     */
    public function subpillar()
    {
        return $this->belongsTo(SubPillar::class, 'link_sub_pillar_id');
    }

    
}
