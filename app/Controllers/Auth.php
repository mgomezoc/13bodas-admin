<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\WelcomeMailer;
use App\Models\ClientModel;
use App\Models\ContentModuleModel;
use App\Models\EventModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;

class Auth extends BaseController
{
    public function __construct(
        private readonly UserModel $userModel = new UserModel(),
        private readonly EventModel $eventModel = new EventModel(),
        private readonly ClientModel $clientModel = new ClientModel(),
        private readonly ContentModuleModel $contentModuleModel = new ContentModuleModel(),
        private readonly WelcomeMailer $welcomeMailer = new WelcomeMailer(),
    ) {
    }

    public function login(): string|ResponseInterface
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->route('admin.dashboard');
        }

        return view('auth/login');
    }

    public function attemptLogin(): ResponseInterface
    {
        $rules = [
            'email' => 'required|valid_email',
            'password' => 'required|min_length[6]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Por favor, completa todos los campos correctamente.');
        }

        $email = (string) $this->request->getPost('email');
        $password = (string) $this->request->getPost('password');
        $user = $this->userModel->findByEmail($email);

        if (!$user || !password_verify($password, (string) $user['password_hash'])) {
            return redirect()->back()->withInput()->with('error', 'Credenciales incorrectas. Verifica tu email y contraseña.');
        }

        if ((int) $user['is_active'] !== 1) {
            return redirect()->back()->with('error', 'Tu cuenta está desactivada. Contacta al administrador.');
        }

        $sessionData = [
            'user_id' => $user['id'],
            'user_name' => $user['full_name'] ?? $user['email'],
            'user_email' => $user['email'],
            'isLoggedIn' => true,
        ];

        session()->set($sessionData);
        $this->userModel->updateLastLogin((string) $user['id']);

        return redirect()->route('admin.dashboard')->with('success', '¡Bienvenido de vuelta!');
    }

    public function register(): string|ResponseInterface
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->route('admin.dashboard');
        }

        return view('auth/register');
    }

    public function processRegister(): ResponseInterface
    {
        $rules = [
            'name' => 'required|min_length[3]|max_length[120]',
            'email' => 'required|valid_email|is_unique[users.email]',
            'phone' => 'permit_empty|max_length[30]',
            'password' => 'required|min_length[6]',
            'password_confirm' => 'required|matches[password]',
            'couple_title' => 'required|min_length[3]|max_length[255]',
            'event_date' => 'required|valid_date[Y-m-d]',
            'terms' => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator?->getErrors() ?? []);
        }

        $db = \Config\Database::connect();
        $db->transException(true)->transStart();

        try {
            $userId = UserModel::generateUUID();
            $this->userModel->insert([
                'id' => $userId,
                'email' => (string) $this->request->getPost('email'),
                'password_hash' => password_hash((string) $this->request->getPost('password'), PASSWORD_BCRYPT),
                'full_name' => (string) $this->request->getPost('name'),
                'phone' => (string) $this->request->getPost('phone'),
                'is_active' => 1,
            ]);

            $this->userModel->assignRole($userId, 4);

            $clientId = UserModel::generateUUID();
            $this->clientModel->insert([
                'id' => $clientId,
                'user_id' => $userId,
                'company_name' => (string) $this->request->getPost('couple_title'),
                'notes' => 'Registro público',
            ]);

            $eventId = $this->eventModel->createEvent([
                'client_id' => $clientId,
                'couple_title' => (string) $this->request->getPost('couple_title'),
                'slug' => $this->eventModel->generateUniqueSlug((string) $this->request->getPost('couple_title')),
                'event_date_start' => (string) $this->request->getPost('event_date') . ' 18:00:00',
                'time_zone' => 'America/Mexico_City',
                'primary_contact_email' => (string) $this->request->getPost('email'),
                'service_status' => 'draft',
                'visibility' => 'private',
                'is_demo' => 1,
                'is_paid' => 0,
            ]);

            if (!$eventId) {
                throw new \RuntimeException('No fue posible crear el evento demo.');
            }

            $this->contentModuleModel->createDefaultModules($eventId);
            $db->transComplete();

            session()->set([
                'user_id' => $userId,
                'user_name' => (string) $this->request->getPost('name'),
                'user_email' => (string) $this->request->getPost('email'),
                'user_roles' => ['client'],
                'client_id' => $clientId,
                'isLoggedIn' => true,
            ]);

            $welcomeResult = $this->welcomeMailer->sendRegistrationWelcome([
                'email' => (string) $this->request->getPost('email'),
                'name' => (string) $this->request->getPost('name'),
                'event_title' => (string) $this->request->getPost('couple_title'),
                'event_date' => (string) $this->request->getPost('event_date'),
                'dashboard_url' => base_url('admin/dashboard'),
                'event_edit_url' => base_url('admin/events/edit/' . $eventId . '?highlight=template'),
                'checkout_url' => base_url('checkout/' . $eventId),
            ]);

            if (!$welcomeResult['success']) {
                log_message('warning', 'Welcome email could not be sent: {message}', ['message' => $welcomeResult['message']]);
            }

            return redirect()->to(base_url('admin/events/edit/' . $eventId . '?highlight=template'))
                ->with('success', '¡Bienvenido! Tu evento se creó en modo DEMO. Elige tu template para continuar.');
        } catch (\Throwable $exception) {
            $db->transRollback();
            log_message('error', 'Public register failed: {message}', ['message' => $exception->getMessage()]);

            return redirect()->back()->withInput()->with('error', 'Error al procesar el registro.');
        }
    }

    public function logout(): ResponseInterface
    {
        session()->destroy();
        return redirect()->route('admin.login')->with('success', 'Has cerrado sesión exitosamente.');
    }
}
