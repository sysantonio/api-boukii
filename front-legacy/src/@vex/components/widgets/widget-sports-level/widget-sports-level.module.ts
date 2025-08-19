import { CUSTOM_ELEMENTS_SCHEMA, NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { ChartModule } from '../../chart/chart.module';
import {MatCardModule} from '@angular/material/card';
import {MatDividerModule} from '@angular/material/divider';
import { WidgetQuickValueCenterCustomModule } from '../widget-quick-value-center-custom/widget-quick-value-center-custom.module';
import { WidgetSportsLevelComponent } from './widget-sports-level.component';
import { WidgetQuickValueCenterCustomLevelModule } from '../widget-quick-value-center-custom-level/widget-quick-value-center-custom-level.module';


@NgModule({
  declarations: [WidgetSportsLevelComponent],
  imports: [
    CommonModule,
    MatButtonModule,
    MatIconModule,
    MatCardModule,
    MatDividerModule,
    WidgetQuickValueCenterCustomLevelModule,
    ChartModule
  ],
  exports: [WidgetSportsLevelComponent]
})
export class WidgetSportsLevelModule {
}
