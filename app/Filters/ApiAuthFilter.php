<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * ApiAuthFilter
 *
 * Validates a Bearer token sent via the Authorization header.
 * Attach to any API route group that requires authentication.
 *
 * Header format:  Authorization: Bearer <token>
 *
 * On success, sets $request->apiUser (the full user row).
 * On failure, returns a 401 JSON response immediately.
 */
class ApiAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $authHeader = $request->getHeaderLine('Authorization');

        if (! str_starts_with($authHeader, 'Bearer ')) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['status' => 'error', 'message' => 'Missing or invalid Authorization header.']);
        }

        $token = trim(substr($authHeader, 7));

        $db  = db_connect();
        $row = $db->table('api_tokens t')
            ->select('t.*, u.id AS user_id, u.fullname, u.username, r.name AS role_name')
            ->join('users u', 'u.id = t.user_id')
            ->join('roles r', 'r.id = u.role_id', 'left')
            ->where('t.token', $token)
            ->where('u.deleted_at IS NULL')
            ->get()
            ->getRowArray();

        if (! $row) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['status' => 'error', 'message' => 'Invalid token.']);
        }

        // Check expiry
        if ($row['expires_at'] && strtotime($row['expires_at']) < time()) {
            $db->table('api_tokens')->where('token', $token)->delete();

            return service('response')
                ->setStatusCode(401)
                ->setJSON(['status' => 'error', 'message' => 'Token has expired.']);
        }

        // Attach user data to request so controllers can read it
        $request->apiUser = $row;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Nothing needed after
    }
}
