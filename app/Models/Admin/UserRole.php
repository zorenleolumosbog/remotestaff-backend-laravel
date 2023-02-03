<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    use HasFactory;

    //Disable timetamps default fields of update_at and create_at 
	public $timestamps = false;

    //Rename table user schema
	protected $table = 'tblm_admin_user_role';

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

    /**
     * Get the admin users for the user role.
     */
    public function adminUsers()
    {
        return $this->hasMany(UserManagement::class, 'link_admin_user_role_id');
    }
}
