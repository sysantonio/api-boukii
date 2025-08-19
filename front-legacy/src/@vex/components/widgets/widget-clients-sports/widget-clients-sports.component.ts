import { Component, Input, OnInit } from '@angular/core';
import { ApexOptions } from '../../chart/chart.component';
import { defaultChartOptions } from '../../../utils/default-chart-options';
import { createDateArray } from '../../../utils/create-date-array';
import Swiper, { Navigation } from 'swiper';

Swiper.use([Navigation]);

@Component({
  selector: 'vex-widget-clients-sports',
  templateUrl: './widget-clients-sports.html',
  styleUrls: ['./widget-clients-sports.component.scss']

})
export class WidgetClientsSportsComponent implements OnInit {

  selectedSport: string = '';
  @Input() data: any = [];
  @Input() type: any;

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

  selectClient(sport: string) {
    this.selectedSport = sport;
}

  ngOnInit() {

  }

}
