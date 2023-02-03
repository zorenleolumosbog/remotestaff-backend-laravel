<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffInvoiceDetail2Add extends Model
{
    use HasFactory;

	//Disable timetamps default fields of update_at and create_at
	public $timestamps = false;

    protected $connection = 'mysql2';

    //Rename table user schema
	protected $table = 'tblm_staff_invoice_dtl_2_add';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'link_staff_invoice_hdr_id',
        'description',
        'quantity',
        'unit_price',
        'createdby',
        'datecreated',
        'modifiedby',
        'datemodified'
    ];
}
