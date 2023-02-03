<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

	//Disable timetamps default fields of update_at and create_at
	public $timestamps = false;

    protected $connection = 'mysql2';

    //Rename table user schema
	protected $table = 'tblm_client';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'client_name',
        'client_poc',
        'client_poc_position',
        'client_towncity',
        'client_addr_line1',
        'client_addr_line2',
        'client_email',
        'client_ABN',
        'client_phone',
        'client_currency',
        'createdby',
        'datecreated',
        'modifiedby',
        'datemodified'
    ];

    /**
     * Get the invoices for the client.
     */
    public function invoiceHeaders()
    {
        return $this->hasMany(ClientInvoiceHeader::class, 'link_client_id')->whereNull('is_void');
    }
}
