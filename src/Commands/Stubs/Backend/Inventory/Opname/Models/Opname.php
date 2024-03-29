<?php

namespace __defaultNamespace__\Models;

use __defaultNamespace__\Models\Related\MCompany;
use __defaultNamespace__\Models\Related\MUser;
use __defaultNamespace__\Models\Related\MWarehouse;
use __defaultNamespace__\Models\Related\StockMutation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Opname extends Model
{
    use HasFactory;
    protected $table = 'inv_opnames';

    // warehouse position
    public function company()
    {
        return $this->belongsTo(MCompany::class, 'company_id')->withTrashed();
    }

    // warehouse position
    public function warehouse()
    {
        return $this->belongsTo(MWarehouse::class, 'warehouse_id')->withTrashed();
    }

    // opname details
    public function details()
    {
        return $this->hasMany(OpnameDetail::class, 'opname_id');
    }

    // adjusted by
    public function createdBy()
    {
        return $this->belongsTo(MUser::class, 'created_by')->withTrashed();
    }

    // adjusted by
    public function adjustedBy()
    {
        return $this->belongsTo(MUser::class, 'adjusted_by')->withTrashed();
    }

    // updated by
    public function updatedBy()
    {
        return $this->belongsTo(MUser::class, 'updated_by')->withTrashed();
    }

    // stock mutation
    public function stockMutations()
    {
        return $this->morphMany(StockMutation::class, 'mutationable');
    }
}
