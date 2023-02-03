<?php
  
namespace App\Models\Users;
  
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
  
class UserVerify extends Model
{
    use HasFactory;
  
    public $table = "tblm_users_verify";
  
    /**
     * Write code on Method
     *
     * @return response()
     */
    protected $fillable = [
        'user_id',
        'token',
    ];
  
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function onboard()
    {
        return $this->belongsTo(Onboard::class, 'user_id', 'id');
    }

    public function onboardBasic()
    {
        return $this->belongsTo(OnboardProfileBasicInfo::class, 'user_id', 'reg_link_preregid');
    }
}