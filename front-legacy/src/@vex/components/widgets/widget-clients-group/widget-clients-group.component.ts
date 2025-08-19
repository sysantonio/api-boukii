import { Component, Input, OnInit } from '@angular/core';
import { ApexOptions } from '../../chart/chart.component';
import { defaultChartOptions } from '../../../utils/default-chart-options';
import { createDateArray } from '../../../utils/create-date-array';
import Swiper, { Navigation } from 'swiper';

Swiper.use([Navigation]);

@Component({
  selector: 'vex-widget-clients-group',
  templateUrl: './widget-clients-group.html',
  styleUrls: ['./widget-clients-group.component.scss']

})
export class WidgetClientsGroupComponent implements OnInit {

  selectedClient: string = '';

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
  @Input() data: any = [];


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

  selectClient(client: string) {
    this.selectedClient = client;
}

  ngOnInit() {

  }

}
