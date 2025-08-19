import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { WidgetSummaryChartsComponent } from './widget-summary-charts.component';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { ChartModule } from '../../chart/chart.module';
import {MatCardModule} from '@angular/material/card';
import {MatDividerModule} from '@angular/material/divider';
import { WidgetQuickValueCenterModule } from '../widget-quick-value-center/widget-quick-value-center.module';


@NgModule({
  declarations: [WidgetSummaryChartsComponent],
  imports: [
    CommonModule,
    MatButtonModule,
    MatIconModule,
    MatCardModule,
    MatDividerModule,
    WidgetQuickValueCenterModule,
    ChartModule
  ],
  exports: [WidgetSummaryChartsComponent]
})
export class WidgetSummaryChartsModule {
}
