import { Component, Input, OnInit } from '@angular/core';
import Swiper, { Navigation } from 'swiper';

Swiper.use([Navigation]);

@Component({
  selector: 'vex-widget-sports-level',
  templateUrl: './widget-sports-level.html',
  styleUrls: ['./widget-sports-level.component.scss']

})
export class WidgetSportsLevelComponent implements OnInit {

  @Input() data?: any = [];
  @Input() level: any;

  constructor() { }

  ngOnInit() {

  }
}
