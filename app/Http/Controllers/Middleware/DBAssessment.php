<?php

namespace App\Http\Middleware;

use Closure;
use Event;
use Illuminate\Contracts\Events\Dispatcher;
use JeroenNoten\LaravelAdminLte\Events\BuildingMenu;

class DBAssessment
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->user()->role == 'admin') {
            Event::listen('JeroenNoten\LaravelAdminLte\Events\BuildingMenu', function ($event) {
                $event->menu->add([
                    'text' => 'Dashboard',
                    'icon' => 'fa fa-home nav-icon',
                    'url'  => 'db-assessment/dashboard',
                    'active' => [
                        'db-assessment/dashboard'
                    ],
                ]);
                $event->menu->add([
                    'text' => 'Setup Data',
                    'icon'    => 'fa fa-database nav-icon',
                    'submenu' => [
                        [
                            'text' => 'Jabatan',
                            'icon' => 'nav-icon mr-4',
                            'url'  => 'db-assessment/positions',
                            'active' => [
                                'db-assessment/positions',
                                'db-assessment/positions/*'
                            ],
                        ],
                        [
                            'text' => 'Divisi',
                            'icon' => 'nav-icon mr-4',
                            'url'  => 'db-assessment/divisions',
                            'active' => [
                                'db-assessment/divisions',
                                'db-assessment/divisions/*'
                            ],
                        ],
                        [
                            'text' => 'Departemen',
                            'icon' => 'nav-icon mr-4',
                            'url'  => 'db-assessment/departements',
                            'active' => [
                                'db-assessment/departements',
                                'db-assessment/departements/*'
                            ],
                        ],
                        [
                            'text' => 'Bidang Usaha',
                            'icon' => 'nav-icon mr-4',
                            'url'  => 'db-assessment/business-fields',
                            'active' => [
                                'db-assessment/business-fields',
                                'db-assessment/business-fields/*'
                            ],
                        ],
                        [
                            'text' => 'Perusahaan',
                            'icon' => 'nav-icon mr-4',
                            'url'  => 'db-assessment/companies',
                            'active' => [
                                'db-assessment/companies',
                                'db-assessment/companies/*'
                            ],
                        ],
                        [
                            'text' => 'Kompetensi',
                            'icon' => 'nav-icon mr-4',
                            'url'  => 'db-assessment/competencies',
                            'active' => [
                                'db-assessment/competencies',
                                'db-assessment/competencies/*'
                            ],
                        ],
                        [
                            'text' => 'Penamaan Rekomendasi',
                            'icon' => 'nav-icon mr-4',
                            'url'  => 'db-assessment/recommendations',
                            'active' => [
                                'db-assessment/recommendations',
                                'db-assessment/recommendations/*'
                            ],
                        ],
                        [
                            'text' => 'Target Job',
                            'icon' => 'nav-icon mr-4',
                            'url'  => 'db-assessment/targetJobs',
                            'active' => [
                                'db-assessment/targetJobs',
                                'db-assessment/targetJobs/*'
                            ],
                        ],
                    ]
                ]);
                $event->menu->add([
                    'text' => 'Data Entry',
                    'icon' => 'fa fa-poll-h nav-icon',
                    'submenu' => [
                        [
                            'text' => 'Data Asesi',
                            'icon' => 'nav-icon mr-4',
                            'url'  => 'db-assessment/participants',
                            'active' => [
                                'db-assessment/participants',
                                'db-assessment/participants/*'
                            ],
                        ],
                        [
                            'text' => 'Nilai Hasil Assessment',
                            'icon' => 'nav-icon mr-4',
                            'url'  => 'db-assessment/assessments',
                            'active' => [
                                'db-assessment/assessments',
                                'db-assessment/assessments/*'
                            ],
                        ]
                    ]
                ]);
                $event->menu->add([
                    'text' => 'Analisa Hasil Assessment',
                    'icon' => 'far fa-file-alt nav-icon',
                    'submenu' => [
                        [
                            'text' => 'Analisa Per Perusahaan',
                            'icon' => 'nav-icon mr-4',
                            'url'  => 'db-assessment/recapsPrint',
                            'active' => [
                                'db-assessment/recapsPrint',
                                'db-assessment/recapsPrint/*',
                                'db-assessment/showParticipantsGap',
                                'db-assessment/showParticipants',
                            ],
                        ],
                        [
                            'text' => 'Komparasi Hasil Assessment',
                            'icon' => 'nav-icon mr-4',
                            'url'  => 'db-assessment/comparisons',
                            'active' => [
                                'db-assessment/comparisons',
                                'db-assessment/comparisons/*'
                            ],
                        ]
                    ]
                ]);
            });
        } else if ($request->user()->role == 'data entry') {
            // return redirect('/home');
            Event::listen('JeroenNoten\LaravelAdminLte\Events\BuildingMenu', function ($event) {
                $event->menu->add([
                    'text' => 'Dashboard',
                    'icon' => 'fa fa-home nav-icon',
                    'url'  => 'db-assessment/dashboard',
                    'active' => [
                        'db-assessment/dashboard'
                    ],
                ]);
                $event->menu->add([
                    'text' => 'Data Entry',
                    'icon' => 'fa fa-poll-h nav-icon',
                    'submenu' => [
                        [
                            'text' => 'Data Asesi',
                            'icon' => 'nav-icon mr-4',
                            'url'  => 'db-assessment/participants',
                            'active' => [
                                'db-assessment/participants',
                                'db-assessment/participants/*'
                            ],
                        ],
                        [
                            'text' => 'Nilai Hasil Assessment',
                            'icon' => 'nav-icon mr-4',
                            'url'  => 'db-assessment/assessments',
                            'active' => [
                                'db-assessment/assessments',
                                'db-assessment/assessments/*'
                            ],
                        ]
                    ]
                ]);
                $event->menu->add([
                    'text' => 'Analisa Hasil Assessment',
                    'icon' => 'far fa-file-alt nav-icon',
                    'submenu' => [
                        [
                            'text' => 'Analisa Per Perusahaan',
                            'icon' => 'nav-icon mr-4',
                            'url'  => 'db-assessment/recapsPrint',
                            'active' => [
                                'db-assessment/recapsPrint',
                                'db-assessment/recapsPrint/*',
                                'db-assessment/showParticipantsGap',
                                'db-assessment/showParticipants',
                            ],
                        ],
                        [
                            'text' => 'Komparasi Hasil Assessment',
                            'icon' => 'nav-icon mr-4',
                            'url'  => 'db-assessment/comparisons',
                            'active' => [
                                'db-assessment/comparisons',
                                'db-assessment/comparisons/*'
                            ],
                        ]
                    ]
                ]);
            });
        }

        return $next($request);
    }
}
