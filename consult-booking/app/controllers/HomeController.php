<?php
declare(strict_types=1);

final class HomeController extends Controller
{
    public function index(): void
    {
        $departments = new Catalog('departments');
        $specialties = new Catalog('specialties');
        $this->view('home/index', [
            'title'       => 'Trang chủ',
            'stats'       => (new User())->stats(),
            'departments' => $departments->all(),
            'specialties' => $specialties->all(),
        ]);
    }
}
