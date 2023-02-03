<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminUserVerify extends Model
{
    use HasFactory;

    //Disable timetamps default fields of update_at and create_at 
	public $timestamps = false;
  
    //Rename table user schema
    public $table = "tblm_admin_user_verify";
  
    /**
     * Write code on Method
     *
     * @return response()
     */
    protected $fillable = [
        'link_admin_user_id',
        'token',
        'datecreated'
    ];
  
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function adminUser()
    {
        return $this->belongsTo(UserManagement::class, 'link_admin_user_id', 'id');
    }
}
