import { Component, Inject, OnInit } from '@angular/core';
import { MAT_DIALOG_DATA } from '@angular/material/dialog';

@Component({
  selector: 'vex-no-monitor-date',
  templateUrl: './no-monitor-date.component.html',
  styleUrls: ['./no-monitor-date.component.scss']
})
export class NoMonitorDateModalComponent implements OnInit {


  constructor(@Inject(MAT_DIALOG_DATA) public defaults: any) {

  }

  ngOnInit(): void {
  }

}
