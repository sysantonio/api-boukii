import { CUSTOM_ELEMENTS_SCHEMA, NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { ChartModule } from '../../chart/chart.module';
import {MatCardModule} from '@angular/material/card';
import {MatDividerModule} from '@angular/material/divider';
import { WidgetQuickValueCenterModule } from '../widget-quick-value-center/widget-quick-value-center.module';
import { WidgetSummaryTasksComponent } from './widget-summary-tasks.component';


@NgModule({
  declarations: [WidgetSummaryTasksComponent],
  imports: [
    CommonModule,
    MatButtonModule,
    MatIconModule,
    MatCardModule,
    MatDividerModule,
    WidgetQuickValueCenterModule,
    ChartModule
  ],
  exports: [WidgetSummaryTasksComponent]
})
export class WidgetSummaryTasksModule {
}
