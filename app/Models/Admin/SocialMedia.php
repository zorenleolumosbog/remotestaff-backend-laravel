<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialMedia extends Model
{
    use HasFactory;

    //Disable timetamps default fields of update_at and create_at 
	public $timestamps = false;

    //Rename table user schema
	protected $table = 'tblm_social_media';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'description',
        'createdby',
        'datecreated',
        'modifiedby',
        'datemodified'
    ];
}
