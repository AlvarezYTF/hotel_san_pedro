<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Catalogo de estados de reserva
        if (!Schema::hasTable('reservation_statuses')) {
            Schema::create('reservation_statuses', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->string('name');
                $table->timestamps();
            });

            DB::table('reservation_statuses')->insert([
                ['code' => 'pending', 'name' => 'Pending', 'created_at' => now(), 'updated_at' => now()],
                ['code' => 'confirmed', 'name' => 'Confirmed', 'created_at' => now(), 'updated_at' => now()],
                ['code' => 'checked_in', 'name' => 'Checked In', 'created_at' => now(), 'updated_at' => now()],
                ['code' => 'checked_out', 'name' => 'Checked Out', 'created_at' => now(), 'updated_at' => now()],
                ['code' => 'cancelled', 'name' => 'Cancelled', 'created_at' => now(), 'updated_at' => now()],
                ['code' => 'no_show', 'name' => 'No Show', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // Catalogo de estados de pago
        if (!Schema::hasTable('payment_statuses')) {
            Schema::create('payment_statuses', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->string('name');
                $table->timestamps();
            });

            DB::table('payment_statuses')->insert([
                ['code' => 'pending', 'name' => 'Pending', 'created_at' => now(), 'updated_at' => now()],
                ['code' => 'partial', 'name' => 'Partial', 'created_at' => now(), 'updated_at' => now()],
                ['code' => 'paid', 'name' => 'Paid', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        Schema::table('reservations', function (Blueprint $table) {
            // Soltar llaves viejas antes de eliminar columnas
            if (Schema::hasColumn('reservations', 'customer_id')) {
                $table->dropForeign(['customer_id']);
            }
            if (Schema::hasColumn('reservations', 'room_id')) {
                $table->dropForeign(['room_id']);
            }

            // Nuevas columnas
            $table->string('reservation_code')->nullable()->unique()->after('id');
            $table->foreignId('client_id')->nullable()->after('reservation_code')->constrained('customers');
            $table->foreignId('status_id')->nullable()->after('client_id')->constrained('reservation_statuses');
            $table->integer('total_guests')->nullable()->after('status_id');
            $table->integer('adults')->nullable()->after('total_guests');
            $table->integer('children')->nullable()->after('adults');
            $table->decimal('deposit_amount', 12, 2)->default(0)->after('total_amount');
            $table->decimal('balance_due', 12, 2)->default(0)->after('deposit_amount');
            $table->foreignId('payment_status_id')->nullable()->after('balance_due')->constrained('payment_statuses');
            $table->enum('source', ['reception', 'web', 'whatsapp', 'ota'])->default('reception')->after('payment_status_id');
            $table->foreignId('created_by')->nullable()->after('source')->constrained('users');
        });

        // Migrar datos existentes a nuevas columnas
        DB::table('reservations')->update([
            'reservation_code' => DB::raw("CONCAT('RSV-', id)"),
            'total_guests' => DB::raw('COALESCE(total_guests, guests_count)')
        ]);

        DB::statement('UPDATE reservations SET client_id = customer_id WHERE customer_id IS NOT NULL');
        DB::statement('UPDATE reservations SET deposit_amount = COALESCE(deposit_amount, deposit, 0)');
        DB::statement('UPDATE reservations SET balance_due = total_amount - deposit_amount WHERE balance_due IS NULL');
        DB::statement('UPDATE reservations SET adults = total_guests WHERE adults IS NULL');
        DB::statement('UPDATE reservations SET children = 0 WHERE children IS NULL');

        // Eliminar columnas antiguas
        Schema::table('reservations', function (Blueprint $table) {
            if (Schema::hasColumn('reservations', 'customer_id')) {
                $table->dropColumn('customer_id');
            }
            if (Schema::hasColumn('reservations', 'room_id')) {
                $table->dropColumn('room_id');
            }
            if (Schema::hasColumn('reservations', 'guests_count')) {
                $table->dropColumn('guests_count');
            }
            if (Schema::hasColumn('reservations', 'deposit')) {
                $table->dropColumn('deposit');
            }
            if (Schema::hasColumn('reservations', 'payment_method')) {
                $table->dropColumn('payment_method');
            }
            if (Schema::hasColumn('reservations', 'reservation_date')) {
                $table->dropColumn('reservation_date');
            }
            if (Schema::hasColumn('reservations', 'check_in_date')) {
                $table->dropColumn('check_in_date');
            }
            if (Schema::hasColumn('reservations', 'check_out_date')) {
                $table->dropColumn('check_out_date');
            }
            if (Schema::hasColumn('reservations', 'check_in_time')) {
                $table->dropColumn('check_in_time');
            }
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            // Restaurar columnas antiguas
            $table->unsignedBigInteger('customer_id')->nullable()->after('id');
            $table->unsignedBigInteger('room_id')->nullable()->after('customer_id');
            $table->integer('guests_count')->nullable()->after('room_id');
            $table->decimal('deposit', 12, 2)->default(0)->after('total_amount');
            $table->string('payment_method')->nullable()->after('deposit');
            $table->date('reservation_date')->nullable()->after('payment_method');
            $table->date('check_in_date')->nullable()->after('reservation_date');
            $table->date('check_out_date')->nullable()->after('check_in_date');
            $table->time('check_in_time')->nullable()->after('check_out_date');

            // Llaves foraneas originales
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('cascade');
        });

        DB::statement('UPDATE reservations SET customer_id = client_id WHERE customer_id IS NULL');
        DB::statement('UPDATE reservations SET guests_count = total_guests WHERE guests_count IS NULL');
        DB::statement('UPDATE reservations SET deposit = deposit_amount WHERE deposit IS NULL');

        Schema::table('reservations', function (Blueprint $table) {
            // Quitar columnas nuevas
            if (Schema::hasColumn('reservations', 'reservation_code')) {
                $table->dropColumn('reservation_code');
            }
            if (Schema::hasColumn('reservations', 'client_id')) {
                $table->dropColumn('client_id');
            }
            if (Schema::hasColumn('reservations', 'status_id')) {
                $table->dropColumn('status_id');
            }
            if (Schema::hasColumn('reservations', 'total_guests')) {
                $table->dropColumn('total_guests');
            }
            if (Schema::hasColumn('reservations', 'adults')) {
                $table->dropColumn('adults');
            }
            if (Schema::hasColumn('reservations', 'children')) {
                $table->dropColumn('children');
            }
            if (Schema::hasColumn('reservations', 'deposit_amount')) {
                $table->dropColumn('deposit_amount');
            }
            if (Schema::hasColumn('reservations', 'balance_due')) {
                $table->dropColumn('balance_due');
            }
            if (Schema::hasColumn('reservations', 'payment_status_id')) {
                $table->dropColumn('payment_status_id');
            }
            if (Schema::hasColumn('reservations', 'source')) {
                $table->dropColumn('source');
            }
            if (Schema::hasColumn('reservations', 'created_by')) {
                $table->dropColumn('created_by');
            }
        });

        Schema::dropIfExists('reservation_statuses');
        Schema::dropIfExists('payment_statuses');
    }
};
