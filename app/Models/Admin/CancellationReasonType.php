<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CancellationReasonType extends Model
{
    use HasFactory;

    public $timestamps = false;

	protected $table = 'tblm_cancellation_reason_type';

    protected $fillable = [
        'link_contract_status_id',
        'description',
        'createdby',
        'datecreated',
        'modifiedby',
        'datemodified'
    ];

    public function contract_status()
    {
        return $this->hasOne(ContractStatus::class, 'id', 'link_contract_status_id');
    }
}
