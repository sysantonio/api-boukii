import { Component, Input, OnInit } from '@angular/core';
import { ApexOptions } from '../../chart/chart.component';
import { defaultChartOptions } from '../../../utils/default-chart-options';
import { createDateArray } from '../../../utils/create-date-array';
import Swiper, { Navigation } from 'swiper';

Swiper.use([Navigation]);

@Component({
  selector: 'vex-widget-summary-tasks',
  templateUrl: './widget-summary-tasks.html',
  styleUrls: ['./widget-summary-tasks.component.scss']

})
export class WidgetSummaryTasksComponent implements OnInit {

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

  ngAfterViewInit() {
    const mySwiper = new Swiper('.swiper-container', {
      slidesPerView: 4,     // Muestra 4 slides a la vez
      slidesPerGroup: 4,   // Desplaza 4 slides a la vez
      navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
      },
    });
  }

  ngOnInit() {

  }

}
