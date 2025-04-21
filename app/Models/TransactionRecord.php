<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionRecord extends Model
{
    protected $table = 'transactions';

    // Get customer and their related transactions
    public static function getCustomerTransactions($customer_id)
    {
        return Customer::with('transactions')
            ->where('customer_id', $customer_id)
            ->first();
    }
}
?>
