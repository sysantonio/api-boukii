import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { WidgetLargeChartComponent } from './widget-large-chart.component';
import { MatIconModule } from '@angular/material/icon';
import { MatButtonModule } from '@angular/material/button';
import { ChartModule } from '../../chart/chart.module';
import { TranslateModule } from '@ngx-translate/core';


@NgModule({
  declarations: [WidgetLargeChartComponent],
  imports: [
    CommonModule,
    MatIconModule,
    MatButtonModule,
    TranslateModule,
    ChartModule
  ],
  exports: [WidgetLargeChartComponent]
})
export class WidgetLargeChartModule {
}
