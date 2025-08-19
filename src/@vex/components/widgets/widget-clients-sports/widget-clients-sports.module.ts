import { CUSTOM_ELEMENTS_SCHEMA, NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { ChartModule } from '../../chart/chart.module';
import {MatCardModule} from '@angular/material/card';
import {MatDividerModule} from '@angular/material/divider';
import { WidgetClientsSportsComponent } from './widget-clients-sports.component';
import { WidgetQuickValueCenterCustomModule } from '../widget-quick-value-center-custom/widget-quick-value-center-custom.module';


@NgModule({
  declarations: [WidgetClientsSportsComponent],
  imports: [
    CommonModule,
    MatButtonModule,
    MatIconModule,
    MatCardModule,
    MatDividerModule,
    WidgetQuickValueCenterCustomModule,
    ChartModule
  ],
  exports: [WidgetClientsSportsComponent]
})
export class WidgetClientsSportsModule {
}
