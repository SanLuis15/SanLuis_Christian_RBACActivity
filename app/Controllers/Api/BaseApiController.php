<?php

namespace App\Controllers\Api;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * BaseApiController
 *
 * All API controllers extend this class.
 * Provides JSON response helpers and a reference to the authenticated user.
 */
class BaseApiController extends Controller
{
    /** @var array|null  Populated by ApiAuthFilter on protected routes */
    protected ?array $apiUser = null;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);

        // Pull the user that ApiAuthFilter attached to the request (may be null on public routes)
        $this->apiUser = $request->apiUser ?? null;
    }

    // ── Response helpers ──────────────────────────────────────────────────────

    protected function ok(mixed $data = null, string $message = 'OK'): ResponseInterface
    {
        return $this->response
            ->setStatusCode(200)
            ->setJSON(['status' => 'success', 'message' => $message, 'data' => $data]);
    }

    protected function created(mixed $data = null, string $message = 'Created'): ResponseInterface
    {
        return $this->response
            ->setStatusCode(201)
            ->setJSON(['status' => 'success', 'message' => $message, 'data' => $data]);
    }

    protected function notFound(string $message = 'Not found'): ResponseInterface
    {
        return $this->response
            ->setStatusCode(404)
            ->setJSON(['status' => 'error', 'message' => $message]);
    }

    protected function badRequest(string $message = 'Bad request', mixed $errors = null): ResponseInterface
    {
        return $this->response
            ->setStatusCode(400)
            ->setJSON(['status' => 'error', 'message' => $message, 'errors' => $errors]);
    }

    protected function forbidden(string $message = 'Forbidden'): ResponseInterface
    {
        return $this->response
            ->setStatusCode(403)
            ->setJSON(['status' => 'error', 'message' => $message]);
    }
}
