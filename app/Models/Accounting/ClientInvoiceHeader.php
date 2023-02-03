<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientInvoiceHeader extends Model
{
    use HasFactory;

	//Disable timetamps default fields of update_at and create_at
	public $timestamps = false;

    protected $connection = 'mysql2';

    //Rename table user schema
	protected $table = 'tblt_invoice_hdr';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'inv_date',
        'link_client_id',
        'inv_period_from',
        'inv_period_to',
        'gross_amt',
        'perc_discount',
        'discount_amt',
        'net_amt',
        'status_id',
        'is_void',
        'void_reason',
        'voidedby',
        'createdby',
        'datecreated',
        'modifiedby',
        'datemodified'
    ];

    /**
     * Get the client for the invoice header.
     */
    public function client()
    {
        return $this->belongsTo(Client::class, 'link_client_id');
    }

    /**
     * Get the details for the invoice header.
     */
    public function invoiceHeaderDetails()
    {
        return $this->hasMany(ClientInvoiceDetail::class, 'link_inv_hdr');
    }

    /**
     * Get the sub item details for the invoice header.
     */
    public function invoiceHeaderSubDetails()
    {
        return $this->hasMany(ClientInvoiceDetail2Add::class, 'link_invoice_hdr_id');
    }
}
