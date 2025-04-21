<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'transactions';

    protected $fillable = [
        'entry_id',
        'customer_id', // Link to Customer model
        'phone',
        'rate',
        'kg',
        'opening_amount',
        'paid_amount',
        'due_amount',
        'advance_amount',
    ];

    public $timestamps = true;

    // Define relationship with Customer model
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    // Automatically generate entry_id before creating a record
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($transaction) {
            $transaction->entry_id = 'ENTRY' . now()->format('YmdHis') . rand(100, 999);
        });
    }
}
?>
