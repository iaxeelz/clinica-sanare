<ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
    @auth
        {{-- Dashboard --}}
        @can('view_dashboard')
            <li class="nav-item">
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-tachometer-alt"></i>
                    <p>Inicio</p>
                </a>
            </li>
        @endcan

        {{-- Módulo Pacientes --}}
        @canany(['view_patients', 'create_patients', 'edit_patients', 'delete_patients'])
            <li class="nav-item">
                <a href="#" class="nav-link {{ request()->routeIs('patients.*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-user-injured"></i>
                    <p>
                        Pacientes
                        <i class="right fas fa-angle-left"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview">
                    @can('view_patients')
                        <li class="nav-item">
                            <a href="{{ route('patients.index') }}"
                                class="nav-link {{ request()->routeIs('patients.index') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Lista de Pacientes</p>
                            </a>
                        </li>
                    @endcan
                    @can('create_patients')
                        <li class="nav-item">
                            <a href="{{ route('patients.create') }}"
                                class="nav-link {{ request()->routeIs('patients.create') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Nuevo Paciente</p>
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcanany

        {{-- Módulo Médicos --}}
        @canany(['view_doctors', 'create_doctors', 'edit_doctors', 'delete_doctors'])
            <li class="nav-item">
                <a href="#" class="nav-link {{ request()->routeIs('doctors.*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-user-md"></i>
                    <p>
                        Médicos
                        <i class="right fas fa-angle-left"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview">
                    @can('view_doctors')
                        <li class="nav-item">
                            <a href="{{ route('doctors.index') }}"
                                class="nav-link {{ request()->routeIs('doctors.index') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Lista de Médicos</p>
                            </a>
                        </li>
                    @endcan
                    @can('create_doctors')
                        <li class="nav-item">
                            <a href="{{ route('doctors.create') }}"
                                class="nav-link {{ request()->routeIs('doctors.create') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Nuevo Médico</p>
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcanany

        {{-- Módulo Citas --}}
        @canany(['view_all_appointments', 'view_own_appointments', 'view_calendar', 'create_appointments'])
            <li class="nav-item">
                <a href="#" class="nav-link {{ request()->routeIs('appointments.*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-calendar-check"></i>
                    <p>
                        Citas
                        <i class="right fas fa-angle-left"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview">
                    @canany(['view_calendar', 'view_all_appointments', 'view_own_appointments'])
                        <li class="nav-item">
                            <a href="{{ route('appointments.calendar') }}"
                                class="nav-link {{ request()->routeIs('appointments.calendar') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Calendario</p>
                            </a>
                        </li>
                    @endcanany
                    @canany(['view_all_appointments', 'view_own_appointments'])
                        <li class="nav-item">
                            <a href="{{ route('appointments.index') }}"
                                class="nav-link {{ request()->routeIs('appointments.index') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Lista de Citas</p>
                            </a>
                        </li>
                    @endcanany
                    @can('create_appointments')
                        <li class="nav-item">
                            <a href="{{ route('appointments.create') }}"
                                class="nav-link {{ request()->routeIs('appointments.create') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Nueva Cita</p>
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcanany

        {{-- Mis Horarios (solo para médicos/enfermeras) --}}
        @if(auth()->user()->hasRole(['medico', 'enfermera']))
            <li class="nav-item">
                <a href="{{ route('doctor.schedules') }}" class="nav-link {{ request()->routeIs('doctor.schedules') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-clock"></i>
                    <p>Mis Horarios</p>
                </a>
            </li>
        @endif

        {{-- Módulo Servicios --}}
        @canany(['view_services', 'create_services', 'edit_services', 'delete_services'])
            <li class="nav-item">
                <a href="#" class="nav-link {{ request()->routeIs('services.*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-stethoscope"></i>
                    <p>
                        Servicios
                        <i class="right fas fa-angle-left"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview">
                    @can('view_services')
                        <li class="nav-item">
                            <a href="{{ route('services.index') }}"
                                class="nav-link {{ request()->routeIs('services.index') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Lista de Servicios</p>
                            </a>
                        </li>
                    @endcan
                    @can('create_services')
                        <li class="nav-item">
                            <a href="{{ route('services.create') }}"
                                class="nav-link {{ request()->routeIs('services.create') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Nuevo Servicio</p>
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcanany

        {{-- Módulo Inventario --}}
        @canany(['view_inventory', 'create_inventory', 'edit_inventory', 'delete_inventory'])
            <li class="nav-item">
                <a href="#" class="nav-link {{ request()->routeIs('inventory.*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-boxes"></i>
                    <p>
                        Inventario
                        <i class="right fas fa-angle-left"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview">
                    @can('view_inventory')
                        <li class="nav-item">
                            <a href="{{ route('inventory.index') }}"
                                class="nav-link {{ request()->routeIs('inventory.index') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Lista de Inventario</p>
                            </a>
                        </li>
                    @endcan
                    @can('create_inventory')
                        <li class="nav-item">
                            <a href="{{ route('inventory.create') }}"
                                class="nav-link {{ request()->routeIs('inventory.create') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Nuevo Artículo</p>
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcanany

        {{-- Módulo Finanzas --}}
        @canany(['view_cash_flow', 'create_income', 'create_expense'])
            <li class="nav-item">
                <a href="#"
                    class="nav-link {{ request()->routeIs('incomes.*') || request()->routeIs('expenses.*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-money-bill-wave"></i>
                    <p>
                        Finanzas
                        <i class="right fas fa-angle-left"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview">
                    @can('view_cash_flow')
                        <li class="nav-item">
                            <a href="{{ route('incomes.cash-flow') }}"
                                class="nav-link {{ request()->routeIs('incomes.cash-flow') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Flujo de Caja</p>
                            </a>
                        </li>
                    @endcan
                    @canany(['view_cash_flow', 'create_income'])
                        <li class="nav-item">
                            <a href="{{ route('incomes.index') }}"
                                class="nav-link {{ request()->routeIs('incomes.index') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Ingresos</p>
                            </a>
                        </li>
                    @endcanany
                    @canany(['view_cash_flow', 'create_expense'])
                        <li class="nav-item">
                            <a href="{{ route('expenses.index') }}"
                                class="nav-link {{ request()->routeIs('expenses.index') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Egresos</p>
                            </a>
                        </li>
                    @endcanany
                </ul>
            </li>
        @endcanany

        {{-- Módulo Reportes --}}
        @canany(['view_reports', 'view_doctor_reports', 'view_service_reports'])
            <li class="nav-item">
                <a href="#" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-chart-bar"></i>
                    <p>
                        Reportes
                        <i class="right fas fa-angle-left"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview">
                    @can('view_reports')
                        <li class="nav-item">
                            <a href="{{ route('reports.index') }}"
                                class="nav-link {{ request()->routeIs('reports.index') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
                    @endcan
                    @canany(['view_reports', 'view_doctor_reports'])
                        <li class="nav-item">
                            <a href="{{ route('reports.doctor') }}"
                                class="nav-link {{ request()->routeIs('reports.doctor') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Por Médico</p>
                            </a>
                        </li>
                    @endcanany
                    @canany(['view_reports', 'view_service_reports'])
                        <li class="nav-item">
                            <a href="{{ route('reports.service') }}"
                                class="nav-link {{ request()->routeIs('reports.service') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Por Servicio</p>
                            </a>
                        </li>
                    @endcanany
                    @can('view_reports')
                        <li class="nav-item">
                            <a href="{{ route('reports.patient') }}"
                                class="nav-link {{ request()->routeIs('reports.patient') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Pacientes</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('reports.financial') }}"
                                class="nav-link {{ request()->routeIs('reports.financial') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Financiero</p>
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcanany

        {{-- Administración (solo admin) --}}
        @canany(['manage_users', 'manage_roles'])
            <li class="nav-header">ADMINISTRACIÓN</li>
            @can('manage_users')
                <li class="nav-item">
                    <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-users-cog"></i>
                        <p>Usuarios</p>
                    </a>
                </li>
            @endcan
            @can('manage_roles')
                <li class="nav-item">
                    <a href="{{ route('roles.index') }}" class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-key"></i>
                        <p>Roles y Permisos</p>
                    </a>
                </li>
            @endcan
        @endcanany

    @endauth
</ul>