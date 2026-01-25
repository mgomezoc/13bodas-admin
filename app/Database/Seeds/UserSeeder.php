<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $data = [
            'name'       => 'Admin 13Bodas',
            'email'      => 'admin@13bodas.com',
            'password'   => password_hash('Admin123!', PASSWORD_DEFAULT),
            'role'       => 'admin',
            'is_active'  => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // Insertar usuario admin por defecto
        $this->db->table('users')->insert($data);
        
        echo "Usuario admin creado:\n";
        echo "Email: admin@13bodas.com\n";
        echo "Password: Admin123!\n";
        echo "¡IMPORTANTE: Cambia esta contraseña después del primer inicio de sesión!\n";
    }
}
