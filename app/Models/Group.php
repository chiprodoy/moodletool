<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    protected $fillable=['courseid','idnumber','name','description',
                            'descriptionformat'];
                            //'timecreated','timemodified'];

    protected $casts=[
        'timecreated'=> 'timestamp',
        'timemodified'=> 'timestamp',
    ];

    public function isExist(){
        return $this->where('idnumber',$this->idnumber)->exists();
    }

    public function user(){
        return $this->belongsToMany(User::class,'groups_members','groupid','userid');
    }




}
