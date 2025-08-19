import { Component, OnInit, Inject } from '@angular/core';
import { MAT_DIALOG_DATA } from '@angular/material/dialog';

@Component({
  selector: 'vex-private-dates-dialog',
  templateUrl: './private-dates-dialog.component.html',
  styleUrls: ['./private-dates-dialog.component.scss']
})
export class PrivateDatesDialogComponent implements OnInit {

  durations: string[] = [];

  today = new Date();
  startView = 'month';  // Puedes cambiar esto a 'year' si prefieres una vista diferente al abrir el datepicker.
  hours: string[] = [];
  selectedHour: any;
  selectedDuration: any;
  selectedDateFrom: Date;
  selectedDateTo: Date;

  constructor(@Inject(MAT_DIALOG_DATA) public data: any) {
  }
  ngOnInit(): void {
    this.generateDurations();

    this.generateHours();
  }

  generateHours() {
    for (let i = 8; i <= 17; i++) {
      for (let j = 0; j < 60; j += 5) {
        const formattedHour = `${i.toString().padStart(2, '0')}:${j.toString().padStart(2, '0')}`;
        this.hours.push(formattedHour);
      }
    }
  }

  generateDurations() {
    let minutes = 15;
    const maxMinutes = 7 * 60; // 7 horas en minutos

    while (minutes <= maxMinutes) {
      const hours = Math.floor(minutes / 60);
      const remainingMinutes = minutes % 60;

      const durationString = `${hours ? hours + 'h ' : ''}${remainingMinutes}min`;
      this.durations.push(durationString);

      minutes += 15;
    }
  }

  myHolidayFilter = (d: Date): boolean => {
    if (d !== null) {

      const time=d.getTime();
      return !this.data.holidays.find(x=>x.getTime()==time);
    }
  }
}
