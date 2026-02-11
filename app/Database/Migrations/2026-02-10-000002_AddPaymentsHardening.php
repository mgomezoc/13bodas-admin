<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPaymentsHardening extends Migration
{
    public function up(): void
    {
        if (!$this->db->tableExists('event_payments')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'CHAR',
                    'constraint' => 36,
                ],
                'event_id' => [
                    'type' => 'CHAR',
                    'constraint' => 36,
                    'null' => false,
                ],
                'payment_provider' => [
                    'type' => 'VARCHAR',
                    'constraint' => 30,
                    'null' => false,
                ],
                'payment_reference' => [
                    'type' => 'VARCHAR',
                    'constraint' => 120,
                    'null' => false,
                ],
                'amount' => [
                    'type' => 'DECIMAL',
                    'constraint' => '10,2',
                    'null' => false,
                ],
                'currency' => [
                    'type' => 'CHAR',
                    'constraint' => 3,
                    'null' => false,
                ],
                'status' => [
                    'type' => 'VARCHAR',
                    'constraint' => 30,
                    'null' => false,
                    'default' => 'completed',
                ],
                'customer_email' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                ],
                'customer_name' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                ],
                'payment_method' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'null' => true,
                ],
                'paid_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'expires_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'webhook_received_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'provider_event_id' => [
                    'type' => 'VARCHAR',
                    'constraint' => 120,
                    'null' => true,
                ],
                'webhook_payload' => [
                    'type' => 'LONGTEXT',
                    'null' => true,
                ],
                'notes' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);

            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey(['payment_provider', 'payment_reference'], 'ux_event_payments_provider_reference');
            $this->forge->addKey('event_id', false, false, 'idx_event_payments_event_id');
            $this->forge->addKey('provider_event_id', false, false, 'idx_event_payments_provider_event_id');
            $this->forge->createTable('event_payments', true);
        }
    }

    public function down(): void
    {
        if ($this->db->tableExists('event_payments')) {
            $this->forge->dropTable('event_payments', true);
        }

    }
}
