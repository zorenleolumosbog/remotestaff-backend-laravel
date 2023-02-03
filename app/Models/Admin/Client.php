<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    //Disable timetamps default fields of update_at and create_at
	public $timestamps = false;

    //Rename table user schema
    protected $connection = 'mysql2';

	protected $table = 'tblm_client';
}
