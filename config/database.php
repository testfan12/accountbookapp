<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

$capsule = new Capsule;

$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => '127.0.0.1',
    'database'  => 'account_book',
    'username'  => 'root',
    'password'  => 'mahadev', // Update if necessary
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

// ✅ Ensure database connection works before creating tables
try {
    Capsule::connection()->getPdo();
} catch (Exception $e) {
    die("❌ Database connection failed: " . $e->getMessage() . "\n");
}

// ✅ Create 'admins' table if not exists
if (!Capsule::schema()->hasTable('admins')) {
    Capsule::schema()->create('admins', function (Blueprint $table) {
        $table->increments('id');
        $table->string('username', 50)->unique();
        $table->string('password', 255);
    });

    Capsule::table('admins')->insert([
        'username' => 'admin',
        'password' => password_hash('admin123', PASSWORD_BCRYPT)
    ]);
}

// ✅ Create or modify 'customers' table
if (!Capsule::schema()->hasTable('customers')) {
    Capsule::schema()->create('customers', function (Blueprint $table) {
        $table->increments('id');
        $table->string('name', 100);
        $table->string('phone', 15)->unique();
        $table->text('address')->nullable();
        $table->decimal('final_amount', 10, 2)->default(0);      // renamed
        $table->decimal('due_amount', 10, 2)->default(0);
        $table->decimal('advance_amount', 10, 2)->default(0);
        $table->timestamps();
    });

    echo "✅ 'customers' table created!\n";
} else {
    // Modify existing structure
    if (Capsule::schema()->hasColumn('customers', 'opening_amount')) {
        Capsule::schema()->table('customers', function (Blueprint $table) {
            $table->renameColumn('opening_amount', 'final_amount');
        });
    }
}

// ✅ Create or modify 'transactions' table
if (!Capsule::schema()->hasTable('transactions')) {
    Capsule::schema()->create('transactions', function (Blueprint $table) {
        $table->increments('id');
        $table->unsignedInteger('customer_id');
        $table->string('phone', 15);
        $table->decimal('rate', 10, 2)->nullable(false);  // Not Null
        $table->decimal('kg', 10, 2)->nullable(false);    // Not Null
        $table->decimal('opening_amount', 10, 2);
        $table->decimal('paid_amount', 10, 2)->nullable(); // Allow NULL
        $table->decimal('due_amount', 10, 2);
        $table->decimal('advance_amount', 10, 2);
        $table->timestamps();
        $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
    });

    echo "✅ 'transactions' table created!\n";
} else {
    // Rename pcs to kg and change type if needed
    if (Capsule::schema()->hasColumn('transactions', 'pcs')) {
        Capsule::schema()->table('transactions', function (Blueprint $table) {
            $table->renameColumn('pcs', 'kg');
        });
    }

    Capsule::schema()->table('transactions', function (Blueprint $table) {
        $table->decimal('rate', 10, 2)->nullable(false)->change();
        $table->decimal('kg', 10, 2)->nullable(false)->change();
        $table->decimal('paid_amount', 10, 2)->nullable()->change(); // Allow NULL
    });
}


// Optional: Update customers' final_amount, due_amount, and advance_amount based on transactions

$customers = Capsule::table('customers')->get();

foreach ($customers as $customer) {
    $totals = Capsule::table('transactions')
        ->where('customer_id', $customer->id)
        ->selectRaw('
            SUM(opening_amount) as total_opening,
            SUM(due_amount) as total_due,
            SUM(advance_amount) as total_advance
        ')
        ->first();

    Capsule::table('customers')
        ->where('id', $customer->id)
        ->update([
            'final_amount' => $totals->total_opening ?? 0,
            'due_amount' => $totals->total_due ?? 0,
            'advance_amount' => $totals->total_advance ?? 0
        ]);
}


// echo "✅ All database tables and modifications are applied successfully!\n";

