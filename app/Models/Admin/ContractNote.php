<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractNote extends Model
{
    use HasFactory;

    public $timestamps = false;

	protected $table = 'tblm_contract_notes';

    protected $fillable = [
        'notes',
        'link_filetype_id',
        'filename',
        'path',
        'createdby',
        'datecreated',
        'modifiedby',
        'datemodified'
    ];

    protected $appends = ['img_url','img_size', 'img_type'];

    public function filetype() {
        return $this->hasOne(FileType::class, 'id', 'link_filetype_id');
    }

    public function getImgUrlAttribute() {
        if(! empty($this->path)) {
            return \Storage::disk('public')->url($this->path);
        }
        return null;
    }

    public function getImgSizeAttribute() {
        if(! empty($this->path)) {
            return \Storage::disk('public')->size($this->path);
        }
        return null;
    }

    public function getImgTypeAttribute() {
        if(! empty($this->path)) {
            return \Storage::disk('public')->mimeType($this->path);
        }
        return null;
    }
}
