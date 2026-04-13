<?php

namespace App\Controllers\Api;

use App\Models\StudentModel;

/**
 * API Students Controller
 *
 * GET  /api/v1/students        → paginated list of students
 * GET  /api/v1/students/{id}   → single student profile
 *
 * Requires: Bearer token (teacher or admin or coordinator role)
 */
class StudentsController extends BaseApiController
{
    private StudentModel $studentModel;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->studentModel = new StudentModel();
    }

    // ── GET /api/v1/students ──────────────────────────────────────────────────

    public function index()
    {
        if (! $this->hasAccess()) {
            return $this->forbidden('Only teachers, admins, and coordinators can list students.');
        }

        $students = $this->studentModel->findAll();

        return $this->ok($students);
    }

    // ── GET /api/v1/students/{id} ─────────────────────────────────────────────

    public function show(int $id)
    {
        if (! $this->hasAccess()) {
            return $this->forbidden('Only teachers, admins, and coordinators can view student profiles.');
        }

        $student = $this->studentModel->find($id);

        if (! $student) {
            return $this->notFound("Student #{$id} not found.");
        }

        return $this->ok($student);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /** Allow teachers, admins, and coordinators; block students from the API list. */
    private function hasAccess(): bool
    {
        return $this->apiUser && in_array(strtolower($this->apiUser['role_name']), ['teacher', 'admin', 'coordinator'], true);
    }
}
