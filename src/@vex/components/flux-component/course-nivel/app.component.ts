import { Component, EventEmitter, Input, Output } from '@angular/core';
import { UntypedFormGroup } from '@angular/forms';

@Component({
  selector: 'vex-course-detail-nivel',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.scss'],
})
export class CourseDetailCardNivelComponent {

  @Input() courseFormGroup!: UntypedFormGroup
  @Input() checkbox: boolean = false
  @Output() changeMonitor = new EventEmitter<any>()

  today: Date = new Date()

  find = (array: any[], key: string, value: string) => array.find((a: any) => a[key] === value)
  DateISO = (value: string) => value ? new Date(value).toLocaleString().split(" ")[0].replace("/", ".").replace("/", ".") : ''
  DateDiff = (value1: string, value2: string): number => Math.round((new Date(value2).getTime() - new Date(value1).getTime()) / 1000 / 60 / 60 / 24)
  Date = (v: string): Date => new Date(v)
  numUsersArray(value: number): number[] {
    return Array.from({ length: value }, (_, i) => i);
  }

}
