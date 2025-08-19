import {NgModule} from '@angular/core';
import {RouterModule} from '@angular/router';
import { VexRoutes } from 'src/@vex/interfaces/vex-route.interface';
import { BonusesComponent } from './bonuses.component';
import { BonusesCreateUpdateComponent } from './bonuses-create-update/bonuses-create-update.component';


const routes: VexRoutes = [
  {
    path: '',
    component: BonusesComponent,
    data: {
      toolbarShadowEnabled: true
    }
  },
  {
    path: 'create',
    component: BonusesCreateUpdateComponent,
    data: {
      toolbarShadowEnabled: true
    }
  },
  {
    path: 'update/:id',
    component: BonusesCreateUpdateComponent,
    data: {
      toolbarShadowEnabled: true
    }
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class BonusesRoutingModule {
}
