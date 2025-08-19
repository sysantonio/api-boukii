import { Component, Inject, OnInit } from '@angular/core';
import { MAT_DIALOG_DATA } from '@angular/material/dialog';
import { TranslateService } from '@ngx-translate/core';

@Component({
  selector: 'vex-date-time-dialog',
  templateUrl: './date-time-dialog.component.html',
  styleUrls: ['./date-time-dialog.component.scss']
})
export class DateTimeDialogComponent implements OnInit {

  durations: string[] = [];

  today = new Date();
  startView = 'month';  // Puedes cambiar esto a 'year' si prefieres una vista diferente al abrir el datepicker.
  hours: string[] = [];
  selectedHour: any;
  selectedDuration: any;
  selectedDate: Date;

  constructor(@Inject(MAT_DIALOG_DATA) public data, public TranslateService: TranslateService) { }

  ngOnInit(): void {
    this.generateDurations();
    this.generateHours();

    if (this.data.dates && this.data.dates.length > 0) {
      const lastDate = this.data.dates[this.data.dates.length - 1];
      // Crear un nuevo objeto Date basado en lastDate.date
      const selectedDate = new Date(lastDate.date);

      // Sumar 1 día a la fecha
      selectedDate.setDate(selectedDate.getDate() + 1);

      // Asignar la fecha modificada y otros valores
      this.selectedDate = selectedDate;
      this.selectedHour = lastDate.hour;
      this.selectedDuration = lastDate.duration;
    }
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

      const time = d.getTime();
      return !this.data.holidays.find(x => x.getTime() == time);
    }
  }
}
