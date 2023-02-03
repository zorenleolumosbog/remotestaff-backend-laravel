<?php

namespace App\Models\Timesheet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimesheetAdjHdr extends Model
{
    use HasFactory;

	//Disable timetamps default fields of update_at and create_at 
	public $timestamps = false;

    protected $connection = 'mysql2';
    
    //Rename table user schema
	protected $table = 'tblt_timesheet_adj_hdr';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'tran_date',
        'client_id',
        'subcon_id',
        'isvalid',
        'isposted',
        'isvoid',
        'void_reason',
        'createdby',
        'datecreated',
        'modifiedby',
        'datemodified'
    ];

    public function dtl()
    {
        return $this->hasMany(TimesheetAdjDtl::class, 'link_adj_hdr_id')->join('tblt_timesheet_dtl','tblt_timesheet_dtl.id','=','tblt_timesheet_adj_dtl.link_timesheet_dtl_id');
    }

}
