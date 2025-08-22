import { Routes } from '@angular/router';
import { featureFlagGuard } from '../../../core/guards/feature-flag.guard';

export const monitoringRoutes: Routes = [
  {
    path: '',
    canActivate: [featureFlagGuard],
    data: { requiredFlag: 'enableDebugMode' },
    loadComponent: () => import('../components/monitoring-dashboard.component')
      .then(m => m.MonitoringDashboardComponent)
  }
];