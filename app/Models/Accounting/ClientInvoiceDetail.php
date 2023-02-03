<?php

namespace App\Models\Accounting;

use App\Models\Admin\InvoiceItemType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientInvoiceDetail extends Model
{
    use HasFactory;

	//Disable timetamps default fields of update_at and create_at
	public $timestamps = false;

    protected $connection = 'mysql2';

    //Rename table user schema
	protected $table = 'tblt_invoice_dtl';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'link_inv_hdr',
        'link_invoice_item_type_id',
        'particular',
        'hours_rendered',
        'rate_per_hour',
        'billable_amt',
        'status_id',
        'createdby',
        'datecreated',
        'modifiedby',
        'datemodified'
    ];

    /**
     * Get the invoice item type for the client invoice details.
     */
    public function invoiceItemType()
    {
        return $this->belongsTo(InvoiceItemType::class, 'link_invoice_item_type_id');
    }
}
