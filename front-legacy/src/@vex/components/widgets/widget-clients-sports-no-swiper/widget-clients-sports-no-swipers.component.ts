import { Component, Input, OnInit } from '@angular/core';
import Swiper, { Navigation } from 'swiper';

Swiper.use([Navigation]);

@Component({
  selector: 'vex-widget-clients-sports-no-swiper',
  templateUrl: './widget-clients-sports-no-swiper.html',
  styleUrls: ['./widget-clients-sports-no-swiper.component.scss']

})
export class WidgetClientsSportsNoSwiperComponent implements OnInit {

  selectedSports: number[] = [];
  @Input() data?: any = [];
  @Input() type?: any;
  @Input() sport: any;

  constructor() { }

  ngOnInit() {

  }


  selectSport(sportId: number): void {
    const index = this.selectedSports.indexOf(sportId);
    if (index > -1) {
      this.selectedSports.splice(index, 1);
    } else {
      this.selectedSports.push(sportId);
    }
  }

  isSportSelected(sportId: number): boolean {
    return this.selectedSports.includes(sportId);
  }

}
