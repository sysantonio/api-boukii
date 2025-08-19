import { Component, OnInit, Inject } from '@angular/core';
import { MAT_DIALOG_DATA } from '@angular/material/dialog';

@Component({
  selector: 'vex-reduction-dialog',
  templateUrl: './reduction-dialog.component.html',
  styleUrls: ['./reduction-dialog.component.scss']
})
export class ReductionDialogComponent implements OnInit {

  generatedNumbers: number[] = [];
  selectedDateIndex: number = null;
  percentage: number = null;
  constructor(@Inject(MAT_DIALOG_DATA) public data: any) {
    this.setIterations(data.iterations);
  }


  ngOnInit(): void {
  }


  // Suponiendo que el parámetro que recibes es `iterations`
  setIterations(iterations: number): void {
    this.generatedNumbers = Array.from({ length: iterations }, (_, i) => i);
  }

  getDateFromIndex(index: number): string {
      let newDate = new Date();
      newDate.setDate(newDate.getDate() + index);
      return newDate.toDateString();
  }
}
