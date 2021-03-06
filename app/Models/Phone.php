<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\SoftDeletes;

class Phone extends Model
{
    use CrudTrait;
//    use SoftDeletes;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'phones';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    // protected $fillable = [];
    // protected $hidden = [];
    // protected $dates = [];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */

    public function sex()
    {
        return $this->belongsTo('App\Models\Sex', 'sex_id');
    }

    public function source()
    {
        return $this->belongsTo('App\Models\Source', 'source_id');
    }

    public function region()
    {
        return $this->belongsTo('App\Models\Region', 'region_id');
    }

    public function town()
    {
        return $this->belongsTo('App\Models\Town', 'town_id');
    }

    public function getSource()
    {
        $result = $this->source;

        if (!$result->dt_rec) {
            $result->dt_rec = '2020-01-01';
        }
//        dd($this->source);
        return $result;
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    function getDtRecAttribute()
    {
        return isset($this->source->dt_rec) ? date('d.m.Y', strtotime($this->source->dt_rec)) : date('d.m.Y', strtotime($this->created_at));
    }

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
}
