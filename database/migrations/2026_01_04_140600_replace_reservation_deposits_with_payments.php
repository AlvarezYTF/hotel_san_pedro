<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Catalogo de metodos de pago
        if (!Schema::hasTable('payments_methods')) {
            Schema::create('payments_methods', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->string('name');
                $table->timestamps();
            });
        }

        // Renombrar tabla base si existe
        if (Schema::hasTable('reservation_deposits') && !Schema::hasTable('payments')) {
            Schema::rename('reservation_deposits', 'payments');
        }

        // Crear tabla payments si no existe
        if (!Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('reservation_id')->constrained('reservations')->cascadeOnDelete();
                $table->decimal('amount', 12, 2);
                $table->foreignId('payment_method_id')->nullable()->constrained('payments_methods');
                $table->string('reference')->nullable();
                $table->dateTime('paid_at')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users');
                $table->timestamp('created_at')->useCurrent();
            });
            return;
        }

        // Normalizar estructura sobre tabla payments existente
        // Asegurar FK reservation_id no duplicada
        if (Schema::hasColumn('payments', 'reservation_id')) {
            $fk = DB::selectOne("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'payments' AND COLUMN_NAME = 'reservation_id' AND REFERENCED_TABLE_NAME IS NOT NULL LIMIT 1");
            if ($fk) {
                DB::statement('ALTER TABLE payments DROP FOREIGN KEY ' . $fk->CONSTRAINT_NAME);
            }
        }

        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'reservation_id')) {
                $table->foreign('reservation_id')->references('id')->on('reservations')->onDelete('cascade');
            }

            if (!Schema::hasColumn('payments', 'payment_method_id')) {
                $table->foreignId('payment_method_id')->nullable()->after('amount')->constrained('payments_methods');
            }
            if (!Schema::hasColumn('payments', 'reference')) {
                $table->string('reference')->nullable()->after('payment_method_id');
            }
            if (!Schema::hasColumn('payments', 'paid_at')) {
                $table->dateTime('paid_at')->nullable()->after('reference');
            }
            if (!Schema::hasColumn('payments', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('paid_at')->constrained('users');
            }
            // Ensure created_at exists
            if (!Schema::hasColumn('payments', 'created_at')) {
                $table->timestamp('created_at')->useCurrent();
            }
        });

        // Backfill payment_method_id from legacy payment_method text if present
        if (Schema::hasColumn('payments', 'payment_method')) {
            DB::statement(<<<SQL
UPDATE payments p
LEFT JOIN payments_methods pm ON LOWER(pm.code) = LOWER(p.payment_method)
SET p.payment_method_id = pm.id
WHERE p.payment_method_id IS NULL
SQL);
        }

        // Copiar notas a reference si corresponde
        if (Schema::hasColumn('payments', 'notes')) {
            DB::statement('UPDATE payments SET reference = notes WHERE reference IS NULL');
        }

        // Eliminar columnas antiguas
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'payment_method')) {
                $table->dropColumn('payment_method');
            }
            if (Schema::hasColumn('payments', 'notes')) {
                $table->dropColumn('notes');
            }
            if (Schema::hasColumn('payments', 'updated_at')) {
                $table->dropColumn('updated_at');
            }
        });
    }

    public function down(): void
    {
        // Revert payments to reservation_deposits
        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $table) {
                if (!Schema::hasColumn('payments', 'payment_method')) {
                    $table->string('payment_method')->nullable()->after('amount');
                }
                if (!Schema::hasColumn('payments', 'notes')) {
                    $table->text('notes')->nullable()->after('payment_method');
                }
                if (!Schema::hasColumn('payments', 'updated_at')) {
                    $table->timestamp('updated_at')->nullable()->after('created_at');
                }
            });

            // Backfill inverso
            DB::statement('UPDATE payments SET payment_method = (SELECT code FROM payments_methods pm WHERE pm.id = payment_method_id) WHERE payment_method IS NULL');
            DB::statement('UPDATE payments SET notes = reference WHERE notes IS NULL');

            Schema::table('payments', function (Blueprint $table) {
                if (Schema::hasColumn('payments', 'created_by')) {
                    $table->dropForeign(['created_by']);
                    $table->dropColumn('created_by');
                }
                if (Schema::hasColumn('payments', 'paid_at')) {
                    $table->dropColumn('paid_at');
                }
                if (Schema::hasColumn('payments', 'reference')) {
                    $table->dropColumn('reference');
                }
                if (Schema::hasColumn('payments', 'payment_method_id')) {
                    $table->dropForeign(['payment_method_id']);
                    $table->dropColumn('payment_method_id');
                }
            });

            if (!Schema::hasTable('reservation_deposits')) {
                Schema::rename('payments', 'reservation_deposits');
            }
        }

        if (Schema::hasTable('payments_methods')) {
            Schema::drop('payments_methods');
        }
    }
};
