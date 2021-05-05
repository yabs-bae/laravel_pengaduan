<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    use HasFactory;
    protected $table = 'complaints';
    protected $primaryKey = 'id';

    protected $fillable = [
        'number', 'title', 'description','date','file','status'
    ];

    public function complaint_histories()
    {
        return $this->hasMany(Complaint_histories::class);
    }



}
