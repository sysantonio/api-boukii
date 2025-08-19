import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { WidgetLargeGoalChartComponent } from './widget-large-goal-chart.component';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { ChartModule } from '../../chart/chart.module';
import { TranslateModule } from '@ngx-translate/core';


@NgModule({
  declarations: [WidgetLargeGoalChartComponent],
  imports: [
    CommonModule,
    MatButtonModule,
    MatIconModule,
    TranslateModule,
    ChartModule
  ],
  exports: [WidgetLargeGoalChartComponent]
})
export class WidgetLargeGoalChartModule {
}
