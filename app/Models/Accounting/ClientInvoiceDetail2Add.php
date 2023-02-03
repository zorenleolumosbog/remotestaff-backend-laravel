<?php

namespace App\Models\Accounting;

use App\Models\Admin\InvoiceItemType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientInvoiceDetail2Add extends Model
{
    use HasFactory;

	//Disable timetamps default fields of update_at and create_at
	public $timestamps = false;

    protected $connection = 'mysql2';

    //Rename table user schema
	protected $table = 'tblt_invoice_dtl_2_add';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'link_invoice_hdr_id',
        'link_invoice_item_type_id',
        'particular',
        'hourly_rate',
        'total_hours',
        'amount_add_on',
        'is_void',
        'void_reason',
        'voidedby',
        'createdby',
        'datecreated',
        'modifiedby',
        'datemodified'
    ];

    /**
     * Get the invoice invoice header for the client invoice details.
     */
    public function invoiceHeader()
    {
        return $this->belongsTo(ClientInvoiceHeader::class, 'link_invoice_hdr_id');
    }

    /**
     * Get the invoice item type for the client invoice details.
     */
    public function invoiceItemType()
    {
        return $this->belongsTo(InvoiceItemType::class, 'link_invoice_item_type_id');
    }
}
