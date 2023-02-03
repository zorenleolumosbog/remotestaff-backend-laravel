<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnboardSpeedtest extends Model
{
    use HasFactory;

    //Disable timetamps default fields of update_at and create_at 
	public $timestamps = false;

    //Rename table user schema
	protected $table = 'tblm_h_onboard_speedtest';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'link_reg_id',
        'latency',
        'download_speed',
        'upload_speed',
        'createdby',
        'datecreated',
        'modifiedby',
        'datemodified'
    ];

    /**
     * Get the speedtest that owns the registrant basic info.
     */
    public function towncity()
    {
        return $this->belongsTo(OnboardProfileBasicInfo::class, 'link_reg_id');
    }
}
