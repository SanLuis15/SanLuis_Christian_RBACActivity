<?php

namespace App\Controllers\Api;

use App\Models\ApiTokenModel;
use App\Models\ApplicationModel;

/**
 * API Auth Controller
 *
 * POST   /api/v1/auth/token   → exchange username+password for a Bearer token
 * DELETE /api/v1/auth/token   → revoke the current token (requires Bearer auth)
 */
class AuthController extends BaseApiController
{
    /** Token lifetime in seconds (default: 24 h) */
    private const TOKEN_TTL = 86400;

    // ── POST /api/v1/auth/token ───────────────────────────────────────────────

    public function issueToken()
    {
        $username = $this->request->getJsonVar('username') ?? $this->request->getPost('username');
        $password = $this->request->getJsonVar('password') ?? $this->request->getPost('password');

        if (empty($username) || empty($password)) {
            return $this->badRequest('username and password are required.');
        }

        $userModel  = new ApplicationModel();
        $user       = $userModel->getUser($username);

        if (! $user || ! password_verify($password, $user['password'])) {
            return $this->response
                ->setStatusCode(401)
                ->setJSON(['status' => 'error', 'message' => 'Invalid credentials.']);
        }

        // Generate a cryptographically secure token
        $token          = bin2hex(random_bytes(32));   // 64-char hex string
        $expiresAt      = date('Y-m-d H:i:s', time() + self::TOKEN_TTL);

        (new ApiTokenModel())->createToken($user['id'], $token, $expiresAt);

        return $this->created([
            'token'      => $token,
            'token_type' => 'Bearer',
            'expires_at' => $expiresAt,
            'user'       => [
                'id'       => $user['id'],
                'name'     => $user['fullname'],
                'username' => $user['username'],
                'role'     => $user['role_name']
            ],
        ], 'Token issued.');
    }

    // ── DELETE /api/v1/auth/token ─────────────────────────────────────────────

    public function revokeToken()
    {
        // ApiAuthFilter already validated the token and set $this->apiUser
        $authHeader = $this->request->getHeaderLine('Authorization');
        $token      = trim(substr($authHeader, 7));

        (new ApiTokenModel())->deleteByToken($token);

        return $this->ok(null, 'Token revoked.');
    }
}
