import { Component, Input, OnInit } from '@angular/core';
import { ApexOptions } from '../../chart/chart.component';
import { defaultChartOptions } from '../../../utils/default-chart-options';
import { createDateArray } from '../../../utils/create-date-array';
import Swiper, { Navigation } from 'swiper';
import { MatTabChangeEvent } from '@angular/material/tabs';

Swiper.use([Navigation]);

@Component({
  selector: 'vex-widget-summary-monitors',
  templateUrl: './widget-summary-monitors.html',
  styleUrls: ['./widget-summary-monitors.component.scss']

})
export class WidgetSummaryMonitorsComponent implements OnInit {

  selectedStatus = 'available';  // Disponible está seleccionado por defecto
  nonDisponiblesSwiperInitialized = false;
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
    const mySwiper = new Swiper('.swiper-container-disponibles', {
      slidesPerView: 4,     // Muestra 4 slides a la vez
      slidesPerGroup: 4,   // Desplaza 4 slides a la vez
      navigation: {
        nextEl: '.swiper-button-next-disponibles',
        prevEl: '.swiper-button-prev-disponibles',
      },
    });
  }

  onTabChange(event: MatTabChangeEvent) {
    // Si el índice de la pestaña es 1 (No Disponibles)
    if (event.index === 1 && !this.nonDisponiblesSwiperInitialized) {
      const mySwiper2 = new Swiper('.swiper-container-non-disponibles', {
        slidesPerView: 4,
        slidesPerGroup: 4,
        navigation: {
          nextEl: '.swiper-button-next-non-disponibles',
          prevEl: '.swiper-button-prev-non-disponibles',
        },
      });
      this.nonDisponiblesSwiperInitialized = true; // Asegúrate de inicializarlo solo una vez
    }
  }

  filterStatus(status: string) {
      this.selectedStatus = status;
      // Aquí va el resto de tu lógica para filtrar según el estado.
  }

  ngOnInit() {

  }

}
