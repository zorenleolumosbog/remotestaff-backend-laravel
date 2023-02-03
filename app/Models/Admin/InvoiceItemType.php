<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItemType extends Model
{
    use HasFactory;

    //Disable timetamps default fields of update_at and create_at
	public $timestamps = false;

    protected $connection = 'mysql';

    //Rename table user schema
	protected $table = 'tblm_invoice_item_type';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'description',
        'is_percentage',
        'percentage',
        'createdby',
        'datecreated',
        'modifiedby',
        'datemodified'
    ];
}
