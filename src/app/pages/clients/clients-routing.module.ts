import {NgModule} from '@angular/core';
import {RouterModule} from '@angular/router';
import { VexRoutes } from 'src/@vex/interfaces/vex-route.interface';
import { ClientsComponent } from './clients.component';
import { ClientCreateUpdateComponent } from './client-create-update/client-create-update.component';
import { ClientDetailComponent } from './client-detail/client-detail.component';


const routes: VexRoutes = [
  {
    path: '',
    component: ClientsComponent,
    data: {
      toolbarShadowEnabled: true
    }
  },
  {
    path: 'create',
    component: ClientCreateUpdateComponent,
    data: {
      toolbarShadowEnabled: true
    }
  },
  {
    path: 'update/:id',
    component: ClientDetailComponent,
    data: {
      toolbarShadowEnabled: true
    }
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class ClientsRoutingModule {
}
