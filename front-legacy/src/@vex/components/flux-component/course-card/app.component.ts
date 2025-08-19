import { Component, EventEmitter, Input, OnChanges, Output } from '@angular/core';
import { UntypedFormGroup } from '@angular/forms';
import { TranslateService } from '@ngx-translate/core';
import { CoursesService } from 'src/service/courses.service';
import { ApiCrudService } from 'src/service/crud.service';
import jsPDF from 'jspdf';
import * as QRCode from 'qrcode';
import { saveAs } from 'file-saver';

@Component({
  selector: 'vex-course-detail-card',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.scss'],
})
export class CourseDetailCardComponent implements OnChanges {

  @Input() courseFormGroup!: UntypedFormGroup
  @Input() onlyExtras: boolean = false
  @Input() noneExtras: boolean = false
  @Input() detail: boolean = false
  @Input() step: number = 0
  @Input() mode: 'create' | 'update' = "create"
  @Output() close = new EventEmitter()
  @Output() open = new EventEmitter<number>()
  @Output() edit = new EventEmitter<number>()

  constructor(public CoursesService: CoursesService, private translateService: TranslateService, private crudService: ApiCrudService) { }

  find = (array: any[], key: string, value: string) => array.find((a: any) => a[key] === value)
  count = (array: any[], key: string) => Boolean(array.map((a: any) => a[key]).find((a: any) => a))
  DateDiff = (value1: string, value2: string): number => Math.round((new Date(value2).getTime() - new Date(value1).getTime()) / 1000 / 60 / 60 / 24)
  ngOnChanges(): void {
    //if (this.courseFormGroup.controls['id'].value) {
    //  const course_dates = []
    //  for (const [index, value] of this.courseFormGroup.controls['course_dates'].value.entries()) {
    //    if (index !== 0 && course_dates[course_dates.length - 1]["price"] === value["price"] &&
    //      course_dates[course_dates.length - 1]["hour_end"] === value["hour_end"] &&
    //      course_dates[course_dates.length - 1]["hour_start"] === value["hour_start"] &&
    //      new Date(value["date"]).getTime() - new Date(course_dates[course_dates.length - 1]["date_end"]).getTime() === 86400000
    //    ) { } else {
    //      course_dates.push(value)
    //    }
    //    course_dates[course_dates.length - 1].date_end = value.date
    //  }
    //  this.courseFormGroup.patchValue({ course_dates })
    //}
  }

  hexToRgb(hex: string) {
    const rgb = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    return rgb ? {
      r: parseInt(rgb[1], 16),
      g: parseInt(rgb[2], 16),
      b: parseInt(rgb[3], 16)
    } : null;
  }

  export(id: any) {
    this.crudService.getFile('/admin/courses/' + id + '/export/' + this.translateService.currentLang)
      .subscribe(async (data) => {
        const blob = new Blob([data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
        saveAs(blob, `course_details_${id}.xlsx`);
      }, error => console.error('Error al descargar el archivo:', error));
  }

  exportQR(id: any) {

    this.crudService.get('/admin/clients/course/' + id)
      .subscribe(async (data) => {
        const clientsData = data.data;
        if (clientsData && clientsData.length) {
          const doc = new jsPDF();
          const pageWidth = doc.internal.pageSize.getWidth();
          const colWidth = pageWidth / 2;
          const lineHeight = 6;
          const qrSize = 48;
          let y = 10;
          for (let i = 0; i < clientsData.length; i++) {
            const client = clientsData[i];
            const isLeftColumn = i % 2 === 0;
            const baseX = isLeftColumn ? 10 : colWidth + 6;
            const qrX = baseX + 48;
            let y_text = y;
            const maxWidthText = 48;
            doc.setTextColor(70, 70, 70);
            doc.setFontSize(16);
            doc.setFont('helvetica', 'bold');
            let lines = doc.splitTextToSize(`${client.client?.first_name} ${client.client?.last_name}`, maxWidthText);
            doc.text(lines, baseX, y_text);
            y_text += (lines.length + 0.4) * lineHeight;
            if (client.client?.phone || client.client?.telephone) {
              let clientPhone = '';
              if (client.client?.phone) { clientPhone = client.client.phone; }
              else { clientPhone = client.client.telephone; }
              doc.setFontSize(14);
              doc.setFont('helvetica', 'normal');
              lines = doc.splitTextToSize(`${clientPhone}`, maxWidthText);
              doc.text(lines, baseX, y_text);
              y_text += lines.length * lineHeight;
            }
            doc.setFontSize(9);
            doc.setFont('helvetica', 'normal');
            lines = doc.splitTextToSize(`${client.course?.name}`, maxWidthText);
            doc.text(lines, baseX, y_text);
            y_text += (lines.length * lineHeight) - 2;
            if (client.monitor) {
              doc.setFontSize(8);
              lines = doc.splitTextToSize(`Professeur - niveau`, maxWidthText);
              doc.text(lines, baseX, y_text);
              y_text += (lines.length * lineHeight) - 2;
              doc.setFontSize(11);
              doc.setFont('helvetica', 'bold');
              lines = doc.splitTextToSize(`${client.monitor?.first_name} ${client.monitor?.last_name}`, maxWidthText);
              doc.text(lines, baseX, y_text);
              y_text += (lines.length * lineHeight) + 3;
            }
            else y_text += 6;
            if (client.degree) {
              const rgbColor = this.hexToRgb(client.degree.color);
              doc.setFillColor(rgbColor.r, rgbColor.g, rgbColor.b);
              doc.setTextColor(255, 255, 255);
              doc.setFontSize(9);
              doc.setFont('helvetica', 'normal');
              const text = `${client.degree?.annotation} - ${client.degree?.name}`;
              lines = doc.splitTextToSize(text, maxWidthText);
              const textBoxHeight = (lines.length + 0.5) * lineHeight;
              doc.rect(baseX, y_text - lineHeight, maxWidthText, textBoxHeight, 'F');
              doc.text(lines, baseX + 1.5, y_text);
              doc.setTextColor(70, 70, 70);
              y_text += textBoxHeight;
            }
            const qrData = await QRCode.toDataURL(client.client.id.toString());
            doc.addImage(qrData, 'JPEG', qrX, y - 10, qrSize, qrSize);
            if (!isLeftColumn || i === clientsData.length - 1) y += qrSize + lineHeight * 4;
            if (y >= doc.internal.pageSize.getHeight() - 20) {
              doc.addPage();
              y = 10;
            }
          }
          doc.save('clients.pdf');
        }
      })
  }
}
