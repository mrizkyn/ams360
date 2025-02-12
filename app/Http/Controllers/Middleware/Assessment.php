<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use JeroenNoten\LaravelAdminLte\Events\BuildingMenu;

class Assessment
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()->role == 'admin') {
            Event::listen(BuildingMenu::class, function ($event) {
                $event->menu->add([
                    'text' => 'Dashboard',
                    'icon' => 'fa fa-home nav-icon',
                    'url'  => 'assessment/dashboard',
                    'active' => ['assessment/dashboard'],
                ]);

                $event->menu->add([
                    'text'    => 'Setup Data',
                    'icon'    => 'fa fa-database nav-icon',
                    'submenu' => [
                        ['text' => 'Jabatan', 'icon' => 'nav-icon mr-4', 'url' => 'assessment/positions', 'active' => ['assessment/positions', 'assessment/positions/*']],
                        ['text' => 'Divisi', 'icon' => 'nav-icon mr-4', 'url' => 'assessment/divisions', 'active' => ['assessment/divisions', 'assessment/divisions/*']],
                        ['text' => 'Departemen', 'icon' => 'nav-icon mr-4', 'url' => 'assessment/departements', 'active' => ['assessment/departements', 'assessment/departements/*']],
                        // ['text' => 'Bidang Usaha', 'icon' => 'nav-icon mr-4', 'url' => 'assessment/business-fields', 'active' => ['assessment/business-fields', 'assessment/business-fields/*']],
                        ['text' => 'Perusahaan', 'icon' => 'nav-icon mr-4', 'url' => 'assessment/companies', 'active' => ['assessment/companies', 'assessment/companies/*']],
                        ['text' => 'Kompetensi', 'icon' => 'nav-icon mr-4', 'url' => 'assessment/competencies', 'active' => ['assessment/competencies', 'assessment/competencies/*', 'assessment/behaviors/*']],
                        ['text' => 'Saran Pengembangan', 'icon' => 'nav-icon mr-4', 'url' => 'assessment/developments-source', 'active' => ['assessment/developments-source', 'assessment/developments-source/*']],
                    ]
                ]);

                $event->menu->add([
                    'text'    => 'Data Entry',
                    'icon'    => 'fa fa-poll-h nav-icon',
                    'submenu' => [
                        ['text' => 'Asesi', 'icon' => 'nav-icon mr-4', 'url' => 'assessment/participants', 'active' => ['assessment/participants', 'assessment/participants/*']],
                        ['text' => 'Proyek', 'icon' => 'nav-icon mr-4', 'url' => url('assessment/projects'), 'active' => ['assessment/projects', 'assessment/projects/*']],
                        ['text' => 'Assessment', 'icon' => 'nav-icon mr-4', 'url' => url('assessment/assessments'), 'active' => ['assessment/assessments', 'assessment/assessments/*']],
                    ]
                ]);
                $event->menu->add([
                    'text'    => 'Setting',
                    'icon'    => 'fa fa-gear nav-icon',
                    'submenu' => [
                        ['text' => 'Kelola User', 'icon' => 'nav-icon mr-4', 'url' => 'assessment/users', 'active' => ['assessment/users', 'assessment/users/*']],
                    ]
                ]);

                // $event->menu->add([
                //     'text'    => 'Laporan',
                //     'icon'    => 'far fa-file-alt nav-icon',
                //     'submenu' => [
                //         ['text' => 'Assessment', 'icon' => 'nav-icon mr-4', 'url' => url('assessment/report-assessment')],
                //         ['text' => 'Perpustakaan Kompetensi', 'icon' => 'nav-icon mr-4', 'url' => 'assessment/report-competence', 'active' => ['assessment/report-competence']],
                //     ]
                // ]);
            });
        } elseif ($request->user()->role == 'data entry') {
            Event::listen(BuildingMenu::class, function ($event) {
                $event->menu->add([
                    'text' => 'Dashboard',
                    'icon' => 'fa fa-home nav-icon',
                    'url'  => 'assessment/dashboard',
                    'active' => ['assessment/dashboard'],
                ]);

                // $event->menu->add([
                //     'text'    => 'Data Entry',
                //     'icon'    => 'fa fa-poll-h nav-icon',
                //     'submenu' => [
                //         ['text' => 'Asesi', 'icon' => 'nav-icon mr-4', 'url' => 'assessment/participants', 'active' => ['assessment/participants', 'assessment/participants/*']],
                //         ['text' => 'Proyek', 'icon' => 'nav-icon mr-4', 'url' => url('assessment/projects'), 'active' => ['assessment/projects', 'assessment/projects/*']],
                //         ['text' => 'Assessment', 'icon' => 'nav-icon mr-4', 'url' => url('assessment/assessments'), 'active' => ['assessment/assessments', 'assessment/assessments/*']],
                //     ]
                // ]);

                // $event->menu->add([
                //     'text'    => 'Laporan',
                //     'icon'    => 'far fa-file-alt nav-icon',
                //     'submenu' => [
                //         ['text' => 'Assessment', 'icon' => 'nav-icon mr-4', 'url' => url('assessment/report-assessment')],
                //         ['text' => 'Perpustakaan Kompetensi', 'icon' => 'nav-icon mr-4', 'url' => 'assessment/report-competence', 'active' => ['assessment/report-competence']],
                //     ]
                // ]);
            });
        }

        return $next($request);
    }
}
