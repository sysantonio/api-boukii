import {NgModule} from '@angular/core';
import {RouterModule} from '@angular/router';
import { VexRoutes } from 'src/@vex/interfaces/vex-route.interface';
import { AdminsComponent } from './admins.component';
import { AdminCreateUpdateComponent } from './admin-create-update/admin-create-update.component';


const routes: VexRoutes = [
  {
    path: '',
    component: AdminsComponent,
    data: {
      toolbarShadowEnabled: true
    }
  },
  {
    path: 'create',
    component: AdminCreateUpdateComponent,
    data: {
      toolbarShadowEnabled: true
    }
  },
  {
    path: 'update/:id',
    component: AdminCreateUpdateComponent,
    data: {
      toolbarShadowEnabled: true
    }
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class AdminsRoutingModule {
}
