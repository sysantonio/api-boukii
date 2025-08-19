import { Injectable } from '@angular/core';
import * as moment from 'moment';

@Injectable({
  providedIn: 'root'
})
export class EventService {

  constructor() { }

  // Método para verificar la superposición entre dos eventos
  isOverlap(events: any, event2: any): any {

    let ret = [];
    let exist = false;
    // Convertir las fechas y horas a objetos moment
    events.forEach((element, idx) => {
      let start1 = moment(element.start);
      let end1 = moment(element.end);
      let start2 = moment(moment(event2.start_date).format('YYYY-MM-DD') + ' ' + moment(event2.start_time, 'HH:mm:ss').format('HH:mm:ss'))
      let end2 = moment(moment(event2.end_date).format('YYYY-MM-DD') + ' ' + moment(event2.end_time, 'HH:mm:ss').format('HH:mm:ss'))

      if (!exist) {
        exist = start1.isBefore(end2) && start2.isBefore(end1);
        if (exist) {
          let newStart = start1;
          let newStart2 = end2;
          let newEnd2 = end1;

          ret.push({
            overlapedId: idx,
            dates: [
              {
                start_date: newStart.format('YYYY-MM-DD'),
                end_date: moment(event2.start_date).format('YYYY-MM-DD'),
                start_time: element.start_time,
                end_time: event2.end_time,
                monitor_id: event2.monitor_id,
                school_id: event2.school_id,
                station_id: event2.station_id,
                full_day: false,
                description: event2.description !== null && event2.description !== '' ? event2.description : element.description,
                color: event2.color,
                user_nwd_subtype_id: event2.user_nwd_subtype_id
              },
              {
                start_date: newStart2.format('YYYY-MM-DD'),
                end_date: newEnd2.format('YYYY-MM-DD'),
                start_time: event2.start_time,
                end_time: element.end_time,
                monitor_id: event2.monitor_id,
                school_id: event2.school_id,
                station_id: event2.station_id,
                full_day: false,
                description: element.description,
                color: event2.color,
                user_nwd_subtype_id: event2.user_nwd_subtype_id
              }]
            }
          )
        }
      }
    });

    // Verificar si hay superposición
    return ret;
  }
}
