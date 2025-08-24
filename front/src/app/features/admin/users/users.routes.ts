import { Routes } from '@angular/router';

export const USERS_ROUTES: Routes = [
  {
    path: '',
    loadComponent: () => import('./users-list.page').then(m => m.UsersListPageComponent)
  },
  {
    path: ':id',
    loadComponent: () => import('./user-detail.page').then(m => m.UserDetailPageComponent)
  }
];