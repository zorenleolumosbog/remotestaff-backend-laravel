<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractServiceType extends Model
{
    use HasFactory;

    public $timestamps = false;

	protected $table = 'tblm_contract_service_types';

    protected $fillable = [
        'description',
        'createdby',
        'datecreated',
        'modifiedby',
        'datemodified'
    ];
}
