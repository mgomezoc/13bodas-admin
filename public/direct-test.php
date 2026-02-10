<?php
/**
 * TEST DIRECTO - BYPASS ROUTING
 * http://localhost/13bodas/public/direct-test.php
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Test Directo</title></head><body>";
echo "<h1>🔧 TEST DIRECTO - BYPASS ROUTING</h1>";
echo "<p><em>Este script bypasea CodeIgniter para testing directo</em></p><hr>";

// Definir constantes necesarias
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
define('SYSTEMPATH', realpath(FCPATH . '../vendor/codeigniter4/framework/system') . DIRECTORY_SEPARATOR);
define('APPPATH', realpath(FCPATH . '../app') . DIRECTORY_SEPARATOR);
define('ROOTPATH', realpath(FCPATH . '..') . DIRECTORY_SEPARATOR);
define('WRITEPATH', realpath(FCPATH . '../writable') . DIRECTORY_SEPARATOR);

// Cargar autoloader
require_once ROOTPATH . 'vendor/autoload.php';

// Cargar servicios básicos
require_once SYSTEMPATH . 'Config/DotEnv.php';
(new \CodeIgniter\Config\DotEnv(ROOTPATH))->load();

$action = $_GET['action'] ?? 'menu';

try {
    switch ($action) {
        case 'menu':
            echo "<h2>📋 MENÚ DE PRUEBAS</h2>";
            echo "<ul style='line-height: 2em;'>";
            echo "<li><a href='?action=check_opcache'>1. Verificar OPcache</a></li>";
            echo "<li><a href='?action=check_files'>2. Verificar archivos modificados</a></li>";
            echo "<li><a href='?action=test_models'>3. Test EventPaymentModel directo</a></li>";
            echo "<li><a href='?action=test_payment&session_id=cs_test_a1MR9rAQPL0gIG5jsVLftK6z323cdd3yqfFQsLcC3kxfpUxpmGQxIxOt5d'>4. Test PaymentService (necesita session_id real)</a></li>";
            echo "<li><a href='?action=activate&event_id=3478948d-d432-4580-b766-af55cf37cf23&payment_ref=pi_3SzIyXHS5fpNSo6T0FKLpJMC'>5. Activar evento manualmente</a></li>";
            echo "</ul>";
            echo "<hr>";
            echo "<h3>ℹ️ Instrucciones:</h3>";
            echo "<ol>";
            echo "<li>Empieza por la opción 1 (OPcache)</li>";
            echo "<li>Si OPcache está activo, reinicia Apache en Laragon</li>";
            echo "<li>Verifica archivos (opción 2)</li>";
            echo "<li>Para activar YA: opción 5</li>";
            echo "<li>Para probar flujo: opciones 3 y 4</li>";
            echo "</ol>";
            break;

        case 'check_opcache':
            echo "<h2>📊 ESTADO OPCACHE</h2>";
            if (function_exists('opcache_get_status')) {
                $status = opcache_get_status(false);
                echo "<p><strong>OPcache habilitado:</strong> " . ($status['opcache_enabled'] ? 'SÍ ❌' : 'NO ✅') . "</p>";
                echo "<p><strong>Revalidate freq:</strong> " . ini_get('opcache.revalidate_freq') . " segundos</p>";
                
                if ($status['opcache_enabled']) {
                    echo "<div style='background: #ffebee; border: 2px solid #f44336; padding: 20px; margin: 20px 0;'>";
                    echo "<h3 style='color: #c62828; margin-top: 0;'>⚠️ OPCACHE ESTÁ ACTIVO</h3>";
                    echo "<p style='font-size: 16px;'>Los cambios en archivos PHP pueden NO aplicarse inmediatamente.</p>";
                    echo "<p><strong>SOLUCIÓN:</strong></p>";
                    echo "<ol>";
                    echo "<li>Abre Laragon</li>";
                    echo "<li>Click en 'Stop All'</li>";
                    echo "<li>Espera 5 segundos</li>";
                    echo "<li>Click en 'Start All'</li>";
                    echo "<li>Recarga esta página</li>";
                    echo "</ol>";
                    echo "</div>";
                    
                    if (function_exists('opcache_reset')) {
                        if (opcache_reset()) {
                            echo "<p style='color: green; font-size: 18px;'>✅ Cache reseteado (pero reiniciar Apache es más seguro)</p>";
                        } else {
                            echo "<p style='color: orange;'>⚠️ No se pudo resetear OPcache (permisos?)</p>";
                        }
                    }
                } else {
                    echo "<p style='color: green; font-size: 18px;'>✅ OPcache NO está activo - Los cambios se aplican inmediatamente</p>";
                }
            } else {
                echo "<p style='color: green; font-size: 18px;'>✅ OPcache NO está disponible en esta instalación</p>";
            }
            
            echo "<hr><p><strong>Configuración PHP actual:</strong></p>";
            echo "<ul>";
            echo "<li><strong>PHP Version:</strong> " . PHP_VERSION . "</li>";
            echo "<li><strong>opcache.enable:</strong> " . ini_get('opcache.enable') . "</li>";
            echo "<li><strong>opcache.revalidate_freq:</strong> " . ini_get('opcache.revalidate_freq') . " segundos</li>";
            echo "</ul>";
            break;

        case 'check_files':
            echo "<h2>📁 ARCHIVOS MODIFICADOS</h2>";
            $files = [
                'EventPaymentModel' => APPPATH . 'Models/EventPaymentModel.php',
                'PaymentService' => APPPATH . 'Libraries/PaymentService.php',
                'Checkout' => APPPATH . 'Controllers/Checkout.php',
            ];
            
            foreach ($files as $name => $path) {
                if (file_exists($path)) {
                    $source = file_get_contents($path);
                    $mtime = filemtime($path);
                    $age = time() - $mtime;
                    
                    echo "<div style='border: 1px solid #ddd; padding: 15px; margin: 10px 0; background: #f9f9f9;'>";
                    echo "<h3 style='margin-top: 0;'>$name</h3>";
                    echo "<p><strong>Última modificación:</strong> " . date('Y-m-d H:i:s', $mtime) . " (hace " . round($age/60, 1) . " minutos)</p>";
                    
                    if ($name === 'EventPaymentModel') {
                        if (strpos($source, '$this->generateUUID()') !== false) {
                            echo "<p style='color: green; font-size: 16px;'>✅ USA \$this->generateUUID() (CORRECTO)</p>";
                            
                            // Verificar si método existe
                            if (strpos($source, 'protected function generateUUID()') !== false) {
                                echo "<p style='color: green;'>✅ Método generateUUID() existe</p>";
                            } else {
                                echo "<p style='color: red;'>❌ Método generateUUID() NO encontrado</p>";
                            }
                        } else if (strpos($source, 'UserModel::generateUUID()') !== false) {
                            echo "<p style='color: red; font-size: 16px;'>❌ Todavía usa UserModel::generateUUID() (CÓDIGO VIEJO)</p>";
                            echo "<p><strong>→ REINICIA Apache en Laragon</strong></p>";
                        } else {
                            echo "<p style='color: orange;'>⚠️ No se pudo determinar</p>";
                        }
                    }
                    
                    if ($name === 'PaymentService') {
                        $count = substr_count($source, "log_message('info', 'PaymentService::");
                        echo "<p style='color: " . ($count > 0 ? 'green' : 'red') . "; font-size: 16px;'>";
                        echo ($count > 0 ? '✅' : '❌') . " Tiene $count logs de PaymentService</p>";
                    }
                    
                    if ($name === 'Checkout') {
                        if (strpos($source, "Checkout::success CALLED") !== false) {
                            echo "<p style='color: green; font-size: 16px;'>✅ Tiene logging en success()</p>";
                        } else {
                            echo "<p style='color: red; font-size: 16px;'>❌ NO tiene logging en success()</p>";
                        }
                    }
                    
                    echo "</div>";
                } else {
                    echo "<div style='border: 1px solid #f44336; padding: 15px; margin: 10px 0; background: #ffebee;'>";
                    echo "<h3 style='color: #c62828; margin-top: 0;'>$name</h3>";
                    echo "<p style='color: red;'>❌ ARCHIVO NO ENCONTRADO: $path</p>";
                    echo "</div>";
                }
            }
            break;

        case 'test_models':
            echo "<h2>🧪 TEST EventPaymentModel</h2>";
            
            // Cargar conexión DB
            $db = \Config\Database::connect();
            echo "<p>✅ Conexión a DB: " . $db->database . "</p>";
            
            $model = new \App\Models\EventPaymentModel();
            
            $testData = [
                'event_id' => '3478948d-d432-4580-b766-af55cf37cf23',
                'payment_provider' => 'stripe',
                'payment_reference' => 'pi_TEST_' . time(),
                'amount' => 800.00,
                'currency' => 'MXN',
                'status' => 'completed',
                'customer_email' => 'test@example.com',
                'paid_at' => date('Y-m-d H:i:s'),
            ];
            
            echo "<p>Intentando crear pago de prueba...</p>";
            echo "<pre>Datos: " . print_r($testData, true) . "</pre>";
            
            $result = $model->createFromWebhook($testData);
            
            if ($result) {
                echo "<div style='background: #e8f5e9; border: 2px solid #4caf50; padding: 20px; margin: 20px 0;'>";
                echo "<h3 style='color: #2e7d32; margin-top: 0;'>✅ PAGO CREADO EXITOSAMENTE</h3>";
                echo "<p><strong>Payment ID:</strong> $result</p>";
                echo "<p>Verifica en phpMyAdmin:</p>";
                echo "<code style='background: #fff; padding: 10px; display: block;'>SELECT * FROM event_payments WHERE id='$result'</code>";
                echo "</div>";
            } else {
                echo "<div style='background: #ffebee; border: 2px solid #f44336; padding: 20px; margin: 20px 0;'>";
                echo "<h3 style='color: #c62828; margin-top: 0;'>❌ NO SE PUDO CREAR EL PAGO</h3>";
                echo "<p><strong>Errores de validación:</strong></p>";
                echo "<pre>";
                print_r($model->errors());
                echo "</pre>";
                echo "</div>";
            }
            break;

        case 'test_payment':
            $sessionId = $_GET['session_id'] ?? '';
            if ($sessionId === '') {
                echo "<p style='color: red; font-size: 18px;'>ERROR: Falta session_id en URL</p>";
                echo "<p>Ejemplo: ?action=test_payment&session_id=cs_test_XXXXX</p>";
                break;
            }
            
            echo "<h2>🧪 TEST PaymentService::finalizeCheckoutSession()</h2>";
            echo "<p><strong>Session ID:</strong> $sessionId</p><hr>";
            
            $service = new \App\Libraries\PaymentService();
            echo "<p>✅ PaymentService cargado</p>";
            
            $result = $service->finalizeCheckoutSession($sessionId);
            
            echo "<h3>RESULTADO:</h3>";
            echo "<pre style='background: #f5f5f5; padding: 15px; border: 1px solid #ddd;'>";
            print_r($result);
            echo "</pre>";
            
            if ($result['is_paid'] ?? false) {
                echo "<div style='background: #e8f5e9; border: 2px solid #4caf50; padding: 20px; margin: 20px 0;'>";
                echo "<h2 style='color: #2e7d32; margin-top: 0;'>✅ PAGO CONFIRMADO</h2>";
                echo "<p><strong>Event ID:</strong> " . ($result['event_id'] ?? 'N/A') . "</p>";
                echo "<p>Verifica que el evento esté activado en: <a href='/13bodas/public/admin/events'>Admin → Eventos</a></p>";
                echo "</div>";
            } else {
                echo "<div style='background: #fff3e0; border: 2px solid #ff9800; padding: 20px; margin: 20px 0;'>";
                echo "<h2 style='color: #e65100; margin-top: 0;'>⚠️ PAGO NO CONFIRMADO</h2>";
                echo "<p><strong>Status:</strong> " . ($result['payment_status'] ?? 'unknown') . "</p>";
                echo "</div>";
            }
            break;

        case 'activate':
            $eventId = $_GET['event_id'] ?? '';
            $paymentRef = $_GET['payment_ref'] ?? '';
            
            if ($eventId === '' || $paymentRef === '') {
                echo "<p style='color: red; font-size: 18px;'>ERROR: Faltan event_id o payment_ref</p>";
                echo "<p>Ejemplo: ?action=activate&event_id=XXXXX&payment_ref=pi_XXXXX</p>";
                break;
            }
            
            echo "<h2>⚙️ ACTIVACIÓN MANUAL DE EVENTO</h2>";
            echo "<p><strong>Event ID:</strong> $eventId</p>";
            echo "<p><strong>Payment Ref:</strong> $paymentRef</p><hr>";
            
            $eventModel = new \App\Models\EventModel();
            $paymentModel = new \App\Models\EventPaymentModel();
            
            // Verificar evento
            $event = $eventModel->find($eventId);
            if (!$event) {
                echo "<p style='color: red;'>❌ Evento no encontrado</p>";
                break;
            }
            
            echo "<p>✅ Evento encontrado: <strong>{$event['couple_title']}</strong></p>";
            echo "<p>Estado actual: is_demo={$event['is_demo']}, is_paid={$event['is_paid']}</p>";
            
            // Crear pago
            if (!$paymentModel->existsByReference('stripe', $paymentRef)) {
                $paymentId = $paymentModel->insert([
                    'id' => sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                        mt_rand(0, 0xffff),
                        mt_rand(0, 0x0fff) | 0x4000,
                        mt_rand(0, 0x3fff) | 0x8000,
                        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
                    ),
                    'event_id' => $eventId,
                    'payment_provider' => 'stripe',
                    'payment_reference' => $paymentRef,
                    'amount' => 800.00,
                    'currency' => 'MXN',
                    'status' => 'completed',
                    'customer_email' => 'manual@test.com',
                    'payment_method' => 'card',
                    'paid_at' => date('Y-m-d H:i:s'),
                    'webhook_received_at' => date('Y-m-d H:i:s'),
                ]);
                
                if ($paymentId) {
                    echo "<p style='color: green;'>✅ Pago registrado: $paymentId</p>";
                } else {
                    echo "<p style='color: red;'>❌ Error creando pago:</p><pre>";
                    print_r($paymentModel->errors());
                    echo "</pre>";
                }
            } else {
                echo "<p style='color: orange;'>⚠️ Pago ya existe en la base de datos</p>";
            }
            
            // Activar evento
            $eventDate = new DateTime($event['event_date_start']);
            $paidUntil = $eventDate->modify('+30 days')->format('Y-m-d H:i:s');
            
            $updateData = [
                'is_demo' => 0,
                'is_paid' => 1,
                'service_status' => 'active',
                'visibility' => 'public',
                'payment_provider' => 'stripe',
                'payment_reference' => $paymentRef,
                'paid_until' => $paidUntil,
            ];
            
            $result = $eventModel->update($eventId, $updateData);
            
            if ($result) {
                echo "<div style='background: #e8f5e9; border: 3px solid #4caf50; padding: 30px; margin: 30px 0; text-align: center;'>";
                echo "<h2 style='color: #2e7d32; margin-top: 0; font-size: 32px;'>🎉 EVENTO ACTIVADO EXITOSAMENTE</h2>";
                echo "<ul style='text-align: left; display: inline-block;'>";
                echo "<li><strong>is_demo:</strong> 0 (desactivado)</li>";
                echo "<li><strong>is_paid:</strong> 1 (pagado)</li>";
                echo "<li><strong>service_status:</strong> active</li>";
                echo "<li><strong>paid_until:</strong> $paidUntil</li>";
                echo "</ul>";
                echo "<p style='margin-top: 30px;'>";
                echo "<a href='/13bodas/public/admin/events' style='background: #4CAF50; color: white; padding: 15px 40px; text-decoration: none; border-radius: 4px; font-size: 18px; display: inline-block;'>✅ Ver Mis Eventos</a>";
                echo "</p>";
                echo "</div>";
            } else {
                echo "<div style='background: #ffebee; border: 2px solid #f44336; padding: 20px; margin: 20px 0;'>";
                echo "<h3 style='color: #c62828; margin-top: 0;'>❌ ERROR AL ACTUALIZAR EVENTO</h3>";
                $errors = $eventModel->errors();
                if ($errors) {
                    echo "<pre>";
                    print_r($errors);
                    echo "</pre>";
                } else {
                    echo "<p>update() devolvió FALSE pero sin errores de validación</p>";
                }
                echo "</div>";
            }
            break;

        default:
            echo "<p style='color: red;'>Acción no reconocida: $action</p>";
    }
    
} catch (\Throwable $e) {
    echo "<div style='background: #ffebee; border: 3px solid #f44336; padding: 20px; margin: 20px 0;'>";
    echo "<h2 style='color: #c62828;'>❌ ERROR CRÍTICO</h2>";
    echo "<p><strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Archivo:</strong> " . htmlspecialchars($e->getFile()) . " (línea " . $e->getLine() . ")</p>";
    echo "<details><summary><strong>Stack trace (click para expandir)</strong></summary>";
    echo "<pre style='background: #f5f5f5; padding: 10px; overflow-x: auto;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</details>";
    echo "</div>";
}

echo "<hr><p><a href='?action=menu' style='background: #2196F3; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;'>← Volver al menú</a></p>";
echo "</body></html>";
