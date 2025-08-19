import { Component, Input, OnInit } from '@angular/core';
import { ApexOptions } from '../../chart/chart.component';
import { defaultChartOptions } from '../../../utils/default-chart-options';
import { createDateArray } from '../../../utils/create-date-array';

@Component({
  selector: 'vex-widget-summary',
  templateUrl: './widget-summary.html'
})
export class WidgetSummaryComponent implements OnInit {

  @Input() total: string;
  @Input() series: ApexNonAxisChartSeries | ApexAxisChartSeries;
  @Input() options: ApexOptions = defaultChartOptions({
    grid: {
      show: true,
      strokeDashArray: 3,
      padding: {
        left: 16
      }
    },
    chart: {
      type: 'line',
      height: 300,
      sparkline: {
        enabled: false
      },
      zoom: {
        enabled: false
      }
    },
    stroke: {
      width: 4
    },
    labels: createDateArray(12),
    xaxis: {
      type: 'datetime',
      labels: {
        show: true
      }
    },
    yaxis: {
      labels: {
        show: true
      }
    }
  });

  constructor() { }

  ngOnInit() {
  }

}
