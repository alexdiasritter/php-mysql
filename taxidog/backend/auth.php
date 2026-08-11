<?php
/**
 * Sessão, usuários e autenticação.
 */

/**
 * Sessão com cookie de longa duração — no PWA o "fechar navegador"
 * acontece toda vez que o app sai do primeiro plano, então o cookie
 * de sessão não pode expirar junto com a janela.
 */
class Session
{
    public static function start(): void
    {
        if (session_status() !== PHP_SESSION_NONE) {
            return;
        }

        $lifetime = (int) config('session_lifetime');
        ini_set('session.gc_maxlifetime', (string) $lifetime);

        session_set_cookie_params([
            'lifetime' => $lifetime,
            'path'     => '/',
            'secure'   => (bool) config('secure_cookies'),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        session_start();
    }

    public static function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function destroy(): void
    {
        $_SESSION = [];
        session_destroy();
    }
}

/**
 * Consultas na tabela `users`.
 */
class Users
{
    public static function findByUsername(string $username): ?array
    {
        $stmt = db()->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute([$username]);
        return $stmt->fetch() ?: null;
    }

    public static function findByRememberSelector(string $selector): ?array
    {
        $stmt = db()->prepare(
            'SELECT * FROM users WHERE remember_selector = ? AND remember_expires > NOW()'
        );
        $stmt->execute([$selector]);
        return $stmt->fetch() ?: null;
    }

    public static function saveRememberToken(int $userId, string $selector, string $hash, string $expiresAt): void
    {
        $stmt = db()->prepare(
            'UPDATE users SET remember_selector = ?, remember_token_hash = ?, remember_expires = ? WHERE id = ?'
        );
        $stmt->execute([$selector, $hash, $expiresAt, $userId]);
    }

    public static function clearRememberToken(int $userId): void
    {
        $stmt = db()->prepare(
            'UPDATE users SET remember_selector = NULL, remember_token_hash = NULL, remember_expires = NULL WHERE id = ?'
        );
        $stmt->execute([$userId]);
    }

    public static function updatePasswordHash(string $username, string $hash): bool
    {
        $stmt = db()->prepare('UPDATE users SET password_hash = ? WHERE username = ?');
        $stmt->execute([$hash, $username]);
        return $stmt->rowCount() > 0;
    }
}

/**
 * Login, logout e o cookie "lembrar-me".
 */
class Auth
{
    public static function check(): bool
    {
        return Session::has('user_id');
    }

    public static function id(): ?int
    {
        $id = Session::get('user_id');
        return $id !== null ? (int) $id : null;
    }

    public static function username(): string
    {
        return (string) Session::get('username', '');
    }

    /** Bloqueia a página se não estiver logado */
    public static function requireLogin(): void
    {
        if (!self::check()) {
            redirect(page_url('index.php'));
        }
    }

    /** Mesma coisa, mas para endpoints JSON */
    public static function requireLoginJson(): void
    {
        if (!self::check()) {
            json_response(['error' => 'Não autenticado'], 401);
        }
    }

    public static function login(string $username, string $password): array
    {
        if ($username === '' || $password === '') {
            return self::loginError('Erro: Usuário ou senha vazios.');
        }

        try {
            $user = Users::findByUsername($username);

            if (!$user) {
                return self::loginError('Erro: Usuário não encontrado no banco de dados.');
            }

            if (!self::passwordMatches($password, (string) $user['password_hash'])) {
                return self::loginError('Erro: Senha incorreta.');
            }

            session_regenerate_id(true);
            Session::set('user_id', (int) $user['id']);
            Session::set('username', $user['username']);
            self::createRememberToken((int) $user['id']);

            return ['success' => true, 'message' => 'Login realizado com sucesso!'];
        } catch (Exception $e) {
            if (config('debug')) {
                return ['success' => false, 'message' => 'Erro de banco de dados: ' . $e->getMessage()];
            }
            return ['success' => false, 'message' => 'Não foi possível entrar. Tente novamente.'];
        }
    }

    /**
     * Aceita tanto senha em texto puro (formato antigo do banco) quanto
     * hash gerado por password_hash(). Assim dá para migrar sem derrubar login.
     */
    private static function passwordMatches(string $password, string $stored): bool
    {
        if (preg_match('/^\$(2[axy]|argon2)/', $stored) === 1) {
            return password_verify($password, $stored);
        }
        return hash_equals($stored, $password);
    }

    /** Em produção não entrega pista de qual campo errou */
    private static function loginError(string $detailedMessage): array
    {
        return [
            'success' => false,
            'message' => config('debug') ? $detailedMessage : 'Usuário ou senha inválidos.',
        ];
    }

    public static function logout(): void
    {
        if (self::check()) {
            Users::clearRememberToken((int) self::id());
        }
        self::clearRememberCookie();
        Session::destroy();
        redirect(page_url('index.php'));
    }

    /** Suporta o logout via ?action=logout em qualquer página */
    public static function handleLogoutRequest(): void
    {
        if (($_GET['action'] ?? '') === 'logout') {
            self::logout();
        }
    }

    /** Gera token novo, salva o hash no banco e manda o cookie */
    public static function createRememberToken(int $userId): void
    {
        $selector  = bin2hex(random_bytes(8));
        $validator = bin2hex(random_bytes(32));
        $lifetime  = (int) config('remember.lifetime');

        Users::saveRememberToken(
            $userId,
            $selector,
            hash('sha256', $validator),
            date('Y-m-d H:i:s', time() + $lifetime)
        );

        setcookie(config('remember.cookie'), $selector . ':' . $validator, [
            'expires'  => time() + $lifetime,
            'path'     => '/',
            'secure'   => (bool) config('secure_cookies'),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    public static function clearRememberCookie(): void
    {
        $name = config('remember.cookie');
        setcookie($name, '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'secure'   => (bool) config('secure_cookies'),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        unset($_COOKIE[$name]);
    }

    /** Sessão expirada + cookie válido = reloga e rotaciona o token */
    public static function attemptRememberLogin(): void
    {
        $cookieName = config('remember.cookie');

        if (self::check() || empty($_COOKIE[$cookieName])) {
            return;
        }

        $parts = explode(':', $_COOKIE[$cookieName]);
        if (count($parts) !== 2) {
            self::clearRememberCookie();
            return;
        }

        [$selector, $validator] = $parts;
        $user = Users::findByRememberSelector($selector);

        if (!$user || !hash_equals((string) $user['remember_token_hash'], hash('sha256', $validator))) {
            self::clearRememberCookie();
            return;
        }

        session_regenerate_id(true);
        Session::set('user_id', (int) $user['id']);
        Session::set('username', $user['username']);
        self::createRememberToken((int) $user['id']);
    }
}
