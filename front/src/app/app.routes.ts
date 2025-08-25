import { Routes } from '@angular/router';
import { authV5Guard } from './core/guards/auth-v5.guard';
import { schoolSelectionGuard } from './core/guards/school-selection.guard';

export const routes: Routes = [
  {
    path: '',
    redirectTo: '/dashboard',
    pathMatch: 'full',
  },
  // All auth routes use AuthShellComponent directly (no auth-layout wrapper)
  {
    path: 'auth/login',
    loadComponent: () => import('./features/auth/pages/login.page').then(m => m.LoginPage)
  },
  {
    path: 'auth/register',
    loadComponent: () => import('./features/auth/pages/register.page').then(m => m.RegisterPage)
  },
  {
    path: 'auth/forgot-password',
    loadComponent: () => import('./features/auth/pages/forgot-password.page').then(m => m.ForgotPasswordPage)
  },
  {
    path: 'dashboard',
    loadComponent: () =>
      import('./features/dashboard/dashboard-page.component').then(c => c.DashboardPageComponent),
    canActivate: [authV5Guard]
  },
  {
    path: 'clients',
    canActivate: [authV5Guard],
    children: [
      {
        path: '',
        loadComponent: () =>
          import('./features/clients/clients-list.page').then(c => c.ClientsListPageComponent)
      },
      {
        path: ':id',
        loadComponent: () =>
          import('./features/clients/client-detail.page').then(c => c.ClientDetailPageComponent)
      }
    ]
  },
  {
    path: 'select-school',
    loadComponent: () =>
      import('./features/school-selection/select-school.page').then(c => c.SelectSchoolPageComponent),
    canActivate: [authV5Guard, schoolSelectionGuard]
  },
  {
    path: 'select-season',
    loadComponent: () => 
      import('./features/dashboard/dashboard-page.component').then(c => c.DashboardPageComponent), // TODO: Replace with actual SeasonSelectionPage
    canActivate: [authV5Guard]
  },
  {
    path: 'admin/users',
    canActivate: [authV5Guard, schoolSelectionGuard],
    loadChildren: () => import('./features/admin/users/users.routes').then(m => m.USERS_ROUTES)
  },
  {
    path: 'admin/roles',
    loadComponent: () => import('./features/admin/roles/roles-list.page').then(m => m.RolesListPageComponent),
    canActivate: [authV5Guard, schoolSelectionGuard]
  },
  {
    path: 'admin/permissions',
    canActivate: [authV5Guard, schoolSelectionGuard],
    loadChildren: () => import('./features/admin/permissions/permissions.routes').then(m => m.permissionsRoutes)
  },
  {
    path: 'unauthorized',
    loadComponent: () =>
      import('./shared/components/unauthorized/unauthorized.component').then(c => c.UnauthorizedComponent)
  },
  {
    path: 'no-access',
    loadComponent: () =>
      import('./shared/components/no-access/no-access.component').then(c => c.NoAccessComponent)
  },
  {
    path: '**',
    redirectTo: '/auth/login',
  },
];
