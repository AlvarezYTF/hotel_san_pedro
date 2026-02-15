<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = "shift_product_outs";

        if (!Schema::hasTable($tableName)) {
            return;
        }

        $hasShiftHandoverId = Schema::hasColumn($tableName, "shift_handover_id");
        $hasUserId = Schema::hasColumn($tableName, "user_id");
        $hasProductId = Schema::hasColumn($tableName, "product_id");
        $hasQuantity = Schema::hasColumn($tableName, "quantity");
        $hasReason = Schema::hasColumn($tableName, "reason");
        $hasObservations = Schema::hasColumn($tableName, "observations");
        $hasShiftType = Schema::hasColumn($tableName, "shift_type");
        $hasShiftDate = Schema::hasColumn($tableName, "shift_date");

        Schema::table($tableName, function (Blueprint $table) use (
            $hasShiftHandoverId,
            $hasUserId,
            $hasProductId,
            $hasQuantity,
            $hasReason,
            $hasObservations,
            $hasShiftType,
            $hasShiftDate
        ) {
            if (!$hasShiftHandoverId) {
                $table->foreignId("shift_handover_id")
                    ->nullable()
                    ->after("id")
                    ->constrained("shift_handovers")
                    ->nullOnDelete();
            }

            if (!$hasUserId) {
                $table->foreignId("user_id")
                    ->nullable()
                    ->after("shift_handover_id")
                    ->constrained("users")
                    ->nullOnDelete();
            }

            if (!$hasProductId) {
                $table->foreignId("product_id")
                    ->after("user_id")
                    ->constrained("products")
                    ->cascadeOnDelete();
            }

            if (!$hasQuantity) {
                $table->decimal("quantity", 12, 2)->default(0)->after("product_id");
            }

            if (!$hasReason) {
                $table->string("reason", 60)->default("otro")->after("quantity");
            }

            if (!$hasObservations) {
                $table->text("observations")->nullable()->after("reason");
            }

            if (!$hasShiftType) {
                $table->string("shift_type", 20)->nullable()->after("observations");
            }

            if (!$hasShiftDate) {
                $table->date("shift_date")->nullable()->after("shift_type");
            }
        });
    }

    public function down(): void
    {
        $tableName = "shift_product_outs";

        if (!Schema::hasTable($tableName)) {
            return;
        }

        $hasShiftHandoverId = Schema::hasColumn($tableName, "shift_handover_id");
        $hasUserId = Schema::hasColumn($tableName, "user_id");
        $hasProductId = Schema::hasColumn($tableName, "product_id");
        $hasQuantity = Schema::hasColumn($tableName, "quantity");
        $hasReason = Schema::hasColumn($tableName, "reason");
        $hasObservations = Schema::hasColumn($tableName, "observations");
        $hasShiftType = Schema::hasColumn($tableName, "shift_type");
        $hasShiftDate = Schema::hasColumn($tableName, "shift_date");

        Schema::table($tableName, function (Blueprint $table) use (
            $hasShiftHandoverId,
            $hasUserId,
            $hasProductId,
            $hasQuantity,
            $hasReason,
            $hasObservations,
            $hasShiftType,
            $hasShiftDate
        ) {
            if ($hasShiftHandoverId) {
                $table->dropConstrainedForeignId("shift_handover_id");
            }

            if ($hasUserId) {
                $table->dropConstrainedForeignId("user_id");
            }

            if ($hasProductId) {
                $table->dropConstrainedForeignId("product_id");
            }

            $columnsToDrop = [];
            if ($hasQuantity) {
                $columnsToDrop[] = "quantity";
            }
            if ($hasReason) {
                $columnsToDrop[] = "reason";
            }
            if ($hasObservations) {
                $columnsToDrop[] = "observations";
            }
            if ($hasShiftType) {
                $columnsToDrop[] = "shift_type";
            }
            if ($hasShiftDate) {
                $columnsToDrop[] = "shift_date";
            }

            if ($columnsToDrop !== []) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};

