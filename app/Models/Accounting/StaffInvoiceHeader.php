<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffInvoiceHeader extends Model
{
    use HasFactory;

	//Disable timetamps default fields of update_at and create_at
	public $timestamps = false;

    protected $connection = 'mysql2';

    //Rename table user schema
	protected $table = 'tblm_staff_invoice_hdr';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'link_client_subcon_pers_id',
        'link_staff_invoice_status_id',
        'invoice_date',
        'rate',
        'invoice_period_from',
        'invoice_period_to',
        'createdby',
        'datecreated',
        'modifiedby',
        'datemodified'
    ];
}
