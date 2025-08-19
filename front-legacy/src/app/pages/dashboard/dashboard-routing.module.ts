import {NgModule} from '@angular/core';
import {RouterModule} from '@angular/router';
import { DashboardComponent } from './dashboard.component';
import { VexRoutes } from 'src/@vex/interfaces/vex-route.interface';
import { DashboardAnalyticsComponent } from './dashboard-analytics/dashboard-analytics.component';


const routes: VexRoutes = [
  {
    path: '',
    component: DashboardAnalyticsComponent,
    data: {
      toolbarShadowEnabled: true
    }
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class DashboardRoutingModule {
}
