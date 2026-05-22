<?php
namespace App\Http\Controllers\Auth;

use App\Core\Controller;
use App\Core\Database;
use App\Services\MailService;
use App\Exceptions\ValidationException;

class PasswordResetController extends Controller
{
    private Database $db;
    private MailService $mail;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->mail = new MailService();
    }

    public function showForgotForm(): void
    {
        $this->view('auth.forgot_password');
    }

    public function sendResetLink(): void
    {
        $email = trim($this->postParam('email', ''));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            set_flash('error', 'Correo electrónico inválido');
            $this->redirect('/olvide-contrasena');
        }

        $user = $this->db->fetchOne(
            "SELECT u.id_usuario, u.nombre_usuario, COALESCE(e.correo, u.nombre_usuario) as email
             FROM usuarios u
             LEFT JOIN empleados e ON u.id_empleado = e.id_empleado
             WHERE e.correo = :email OR u.nombre_usuario = :email2",
            ['email' => $email, 'email2' => $email]
        );

        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $this->db->insert('password_resets', [
            'email' => $email,
            'token' => hash('sha256', $token),
            'expires_at' => $expiresAt,
            'used' => 0,
        ]);

        if ($user) {
            $resetUrl = APP_URL . "/restablecer-contrasena?token={$token}&email=" . urlencode($email);
            $subject = 'Recuperación de contraseña - Plasti Frus';
            $body = $this->buildEmailBody($resetUrl);
            $this->mail->send($email, $subject, $body);
        }

        set_flash('success', 'Si el correo está registrado, recibirás un enlace para restablecer tu contraseña.');
        $this->redirect('/login');
    }

    public function showResetForm(): void
    {
        $token = $this->getParam('token', '');
        $email = $this->getParam('email', '');

        if (!$token || !$email) {
            set_flash('error', 'Enlace inválido o incompleto');
            $this->redirect('/login');
        }

        $record = $this->db->fetchOne(
            "SELECT * FROM password_resets
             WHERE email = :email AND token = :token AND used = 0 AND expires_at > NOW()",
            ['email' => $email, 'token' => hash('sha256', $token)]
        );

        if (!$record) {
            set_flash('error', 'El enlace ha expirado o ya fue utilizado');
            $this->redirect('/login');
        }

        $this->view('auth.reset_password', [
            'token' => $token,
            'email' => $email,
        ]);
    }

    public function resetPassword(): void
    {
        $token = $this->postParam('token', '');
        $email = $this->postParam('email', '');
        $password = $this->postParam('password', '');
        $passwordConfirm = $this->postParam('password_confirm', '');

        if (strlen($password) < 6) {
            set_flash('error', 'La contraseña debe tener al menos 6 caracteres');
            $this->redirect("/restablecer-contrasena?token={$token}&email=" . urlencode($email));
        }

        if ($password !== $passwordConfirm) {
            set_flash('error', 'Las contraseñas no coinciden');
            $this->redirect("/restablecer-contrasena?token={$token}&email=" . urlencode($email));
        }

        $record = $this->db->fetchOne(
            "SELECT * FROM password_resets
             WHERE email = :email AND token = :token AND used = 0 AND expires_at > NOW()",
            ['email' => $email, 'token' => hash('sha256', $token)]
        );

        if (!$record) {
            set_flash('error', 'El enlace ha expirado o ya fue utilizado');
            $this->redirect('/login');
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $user = $this->db->fetchOne(
            "SELECT u.id_usuario FROM usuarios u
             LEFT JOIN empleados e ON u.id_empleado = e.id_empleado
             WHERE e.correo = :email OR u.nombre_usuario = :email2",
            ['email' => $email, 'email2' => $email]
        );

        if ($user) {
            $this->db->update('usuarios', ['password_hash' => $hash], 'id_usuario = :id', ['id' => $user['id_usuario']]);
        }

        $this->db->update('password_resets', ['used' => 1], 'id = :id', ['id' => $record['id']]);

        set_flash('success', 'Contraseña actualizada exitosamente. Ahora puedes iniciar sesión.');
        $this->redirect('/login');
    }

    private function buildEmailBody(string $resetUrl): string
    {
        $appName = APP_NAME;
        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head><meta charset="UTF-8"><title>Recuperación de Contraseña</title></head>
        <body style="font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px;">
            <div style="max-width: 600px; margin: auto; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.1);">
                <div style="background: linear-gradient(135deg, #1a1a2e, #0f3460); color: white; padding: 20px; text-align: center;">
                    <h2 style="margin:0;">{$appName}</h2>
                    <p style="margin:5px 0 0; opacity:.8;">Recuperación de Contraseña</p>
                </div>
                <div style="padding: 30px;">
                    <p>Hemos recibido una solicitud para restablecer tu contraseña.</p>
                    <p>Haz clic en el siguiente botón para crear una nueva contraseña:</p>
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="{$resetUrl}" style="background: #0f3460; color: white; padding: 12px 30px; border-radius: 5px; text-decoration: none; display: inline-block;">
                            Restablecer Contraseña
                        </a>
                    </div>
                    <p style="color: #666; font-size: 13px;">Este enlace expirará en 1 hora. Si no solicitaste este cambio, ignora este mensaje.</p>
                    <hr style="border: none; border-top: 1px solid #eee;">
                    <p style="color: #999; font-size: 12px;">&copy; 2026 {$appName}. Todos los derechos reservados.</p>
                </div>
            </div>
        </body>
        </html>
        HTML;
    }
}
