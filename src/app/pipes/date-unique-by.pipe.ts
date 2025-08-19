import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  name: 'dateUniqueBy'
})
export class DateUniqueByPipe implements PipeTransform {
  transform(value: any[], key: string): any[] {
    if (!Array.isArray(value) || !key) return value;

    const uniqueValues = new Set();
    return value.filter(item => {
      const uniqueKey = item[key];
      if (!uniqueValues.has(uniqueKey)) {
        uniqueValues.add(uniqueKey);
        return true;
      }
      return false;
    });
  }
}
