import { Routes } from '@angular/router';
import { authV5Guard } from './core/guards/auth-v5.guard';
import { schoolSelectionGuard } from './core/guards/school-selection.guard';

export const routes: Routes = [
  {
    path: '',
    redirectTo: '/dashboard',
    pathMatch: 'full',
  },
  // Direct login route (bypasses auth-layout)
  {
    path: 'auth/login',
    loadComponent: () => import('./features/auth/pages/login.page').then(m => m.LoginPage)
  },
  // Other auth routes still use auth-layout
  {
    path: 'auth',
    loadChildren: () => import('./features/auth/auth.routes').then(m => m.authRoutes)
  },
  {
    path: 'dashboard',
    loadComponent: () => 
      import('./features/dashboard/dashboard-page.component').then(c => c.DashboardPageComponent),
    canActivate: [authV5Guard]
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
