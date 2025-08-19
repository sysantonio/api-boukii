import { Routes } from '@angular/router';

export const authRoutes: Routes = [
  {
    path: '',
    loadComponent: () => import('@ui/auth-layout/auth-layout.component').then(c => c.AuthLayoutComponent),
    children: [
      {
        path: '',
        redirectTo: 'register',
        pathMatch: 'full'
      },
      // Note: login route is now handled directly in app.routes.ts
      {
        path: 'register', 
        loadComponent: () => import('./pages/register.page').then(m => m.RegisterPage)
      },
      {
        path: 'forgot-password',
        loadComponent: () => import('./pages/forgot-password.page').then(m => m.ForgotPasswordPage)
      }
    ]
  }
];