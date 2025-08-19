import { Component, OnInit } from '@angular/core';
import { ApiCrudService } from '../../../../service/crud.service';
import { ActivatedRoute, Router } from '@angular/router';
import { CoursesService } from '../../../../service/courses.service';
import { TranslateService } from '@ngx-translate/core';
import { TableColumn } from 'src/@vex/interfaces/table-column.interface';

@Component({
  selector: 'vex-course-detail',
  templateUrl: './course-detail.component.html',
  styleUrls: ['./course-detail.component.scss']
})
export class CourseDetailComponent implements OnInit {
  minDate = new Date(2000, 1, 1);
  nowDate = new Date()
  maxDate = new Date(2099, 12, 31);
  user: any;
  settings: any;
  id: number;
  loading: boolean = true;
  shortDescription: string = '';
  courseName: string = '';
  firstCombinationValue: number | null = null;
  description: string = '';
  ageRange: { age_min: number, age_max: number } = { age_min: 0, age_max: 0 };
  shortestDuration: string | null = null;
  sendEmailModal: boolean = false

  constructor(private crudService: ApiCrudService, private activatedRoute: ActivatedRoute, private router: Router, public courses: CoursesService, public TranslateService: TranslateService) {
    this.user = JSON.parse(localStorage.getItem('boukiiUser'));
    this.settings = JSON.parse(this.user.schools[0].settings);
    this.id = this.activatedRoute.snapshot.params.id;
  }

  detailData: any

  ngOnInit(): void {
    this.crudService.get('/admin/courses/' + this.id,
      ['courseGroups.degree', 'courseGroups.courseDates.courseSubgroups.bookingUsers.client', 'sport'])
      .subscribe((data: any) => {
        this.detailData = data.data
        this.crudService.list('/degrees', 1, 10000, 'asc', 'degree_order', '&school_id=' + this.detailData.school_id + '&sport_id=' + this.detailData.sport_id)
          .subscribe((data) => {
            this.detailData.degrees = [];
            data.data.forEach((element: any) => {
              if (element.active) this.detailData.degrees.push({ ...element, });
            });
            this.detailData.degrees.forEach((level: any) => {
              level.active = false;
              this.detailData.course_dates.forEach((cs: any) => {
                cs.course_groups.forEach((group: any) => {
                  if (group.degree_id === level.id) {
                    level.active = true;
                    level.old = true;
                  } level.visible = false;
                });
              });
            });
            this.crudService.list('/stations', 1, 10000, 'desc', 'id', '&school_id=' + this.detailData.school_id)
              .subscribe((st: any) => {
                st.data.forEach((element: any) => {
                  if (element.id === this.detailData.station_id) this.detailData.station = element
                });
                this.crudService.list('/booking-users', 1, 10000, 'desc', 'id', '&school_id=' + this.detailData.school_id + '&course_id=' + this.detailData.id)
                  .subscribe((bookingUser) => {
                    this.detailData.users = [];
                    this.detailData.users = bookingUser.data;
                    this.courses.settcourseFormGroup(this.detailData)
                    setTimeout(() => this.loading = false, 0);
                  })
              })
          });
      })
  }

  updateCourses() {
    const courseFormGroup = this.courses.courseFormGroup.getRawValue()
    courseFormGroup.translations = JSON.stringify(this.courses.courseFormGroup.controls['translations'].value)
    courseFormGroup.course_type === 1 ? delete courseFormGroup.settings : courseFormGroup.settings = JSON.stringify(this.courses.courseFormGroup.controls['settings'].value)
    courseFormGroup.discounts = JSON.stringify(this.courses.courseFormGroup.controls['discounts'].value)
    if (!courseFormGroup.options) delete courseFormGroup.options;
    this.crudService.update('/admin/courses', courseFormGroup, this.id).subscribe()
  }

  goTo(route: string, query: any = null) {
    this.router.navigate([route], { queryParams: query });
  }

  DateDiff = (value1: string, value2: string): number => Math.round((new Date(value2).getTime() - new Date(value1).getTime()) / 1000 / 60 / 60 / 24)
  count = (array: any[], key: string) => Boolean(array.map((a: any) => a[key]).find((a: any) => a))

  columns: TableColumn<any>[] = [
    { label: 'Id', property: 'id', type: 'id', visible: true, cssClasses: ['font-medium'] },
    { label: 'type', property: 'sport', type: 'booking_users_image', visible: true },
    { label: 'course', property: 'booking_users', type: 'booking_users', visible: true },
    { label: 'client', property: 'client_main', type: 'client', visible: true },
    { label: 'dates', property: 'dates', type: 'booking_dates', visible: true },
    { label: 'register', property: 'created_at', type: 'date', visible: true },
    //{ label: 'Options', property: 'options', type: 'text', visible: true },
    { label: 'op_rem_abr', property: 'has_cancellation_insurance', type: 'light', visible: true },
    { label: 'B. Care', property: 'has_boukii_care', type: 'light', visible: true },
    { label: 'price', property: 'price_total', type: 'price', visible: true },
    { label: 'method_paiment', property: 'payment_method', type: 'payment_method', visible: true },
    { label: 'bonus', property: 'bonus', type: 'light', visible: true },
    { label: 'paid', property: 'paid', type: 'payment_status', visible: true },
    { label: 'status', property: 'status', type: 'cancelation_status', visible: true },
    { label: 'Actions', property: 'actions', type: 'button', visible: true }
  ];

}
