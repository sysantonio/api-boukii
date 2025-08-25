import { Routes } from '@angular/router';
import { authV5Guard } from '../../../core/guards/auth-v5.guard';
import { schoolSelectionGuard } from '../../../core/guards/school-selection.guard';

export const permissionsRoutes: Routes = [
  {
    path: '',
    canActivate: [authV5Guard, schoolSelectionGuard],
    children: [
      {
        path: '',
        redirectTo: 'matrix',
        pathMatch: 'full'
      },
      {
        path: 'matrix',
        loadComponent: () => import('./permissions-page.component').then(m => m.PermissionsPageComponent),
        data: {
          title: 'permissions.title',
          breadcrumbs: [
            { label: 'admin.title', route: '/admin' },
            { label: 'permissions.title', route: '/admin/permissions' }
          ]
        }
      }
    ]
  }
];