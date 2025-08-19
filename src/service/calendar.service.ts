import { Injectable } from '@angular/core';
import { Subject } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class CalendarService {
  private monthChangedSubject = new Subject<Date>();
  monthChanged$ = this.monthChangedSubject.asObservable();

  notifyMonthChanged(firstDayOfMonth: Date) {
    this.monthChangedSubject.next(firstDayOfMonth);
  }
}
