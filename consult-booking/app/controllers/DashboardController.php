<?php
declare(strict_types=1);

/** Cổng vào theo vai trò. Hai trang sinh viên/giảng viên là khung để các module khác gắn chức năng vào. */
final class DashboardController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin();
        redirect(match (Auth::user()['vai_tro']) {
            'admin'    => 'admin/dashboard',
            'lecturer' => 'dashboard/lecturer',
            default    => 'dashboard/student',
        });
    }

    public function student(): void
    {
        Auth::requireRole('student');
        $this->view('dashboard/portal', ['title' => 'Khu vực sinh viên', 'portal' => 'student', 'me' => Auth::user()]);
    }

    public function lecturer(): void
    {
        Auth::requireRole('lecturer');
        $this->view('dashboard/portal', ['title' => 'Khu vực giảng viên', 'portal' => 'lecturer', 'me' => Auth::user()]);
    }
}
