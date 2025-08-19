import { Component, OnInit } from '@angular/core';
import { UntypedFormBuilder, UntypedFormGroup, Validators } from '@angular/forms';
import { MatDialog } from '@angular/material/dialog';
import { map, forkJoin, mergeMap } from 'rxjs';
import { fadeInUp400ms } from 'src/@vex/animations/fade-in-up.animation';
import { stagger20ms } from 'src/@vex/animations/stagger.animation';
import { ApiCrudService } from 'src/service/crud.service';
import { ActivatedRoute, Router } from '@angular/router';
import { SchoolService } from 'src/service/school.service';
import { CoursesService } from 'src/service/courses.service';

@Component({
  selector: 'vex-courses-create-update',
  templateUrl: './courses-create-update.component.html',
  styleUrls: ['./courses-create-update.component.scss',],
  animations: [fadeInUp400ms, stagger20ms]
})
export class CoursesCreateUpdateComponent implements OnInit {
  dataSource: any;

  ModalFlux: number = +this.activatedRoute.snapshot.queryParamMap['params'].step || 0
  ModalProgress: { Name: string, Modal: number }[] = [
    { Name: "sport", Modal: 0 },
    { Name: "details", Modal: 1 },
    { Name: "dates", Modal: 2 },
    { Name: "levels", Modal: 3 },
    { Name: "extras", Modal: 4 },
    { Name: "langs", Modal: 5 },
  ]
  Translate: { Code: string, Name: string }[] = [
    { Code: "fr", Name: "French" },
    { Code: "de", Name: "German" },
    { Code: "en", Name: "English" },
    { Code: "it", Name: "Italian" },
    { Code: "es", Name: "Spanish" },
  ]

  PeriodoFecha: number = 0
  extrasFormGroup: UntypedFormGroup; //crear extras nuevas
  nowDate: Date = new Date()
  sportData: any = [];
  sportDataList: any = [];
  sportTypeData: any = [];
  stations: any = [];
  monitors: any = [];
  schoolData: any = [];
  extras: any = []

  mode: 'create' | 'update' = 'create';
  loading: boolean = true;
  extrasModal: boolean = false
  confirmModal: boolean = false
  editModal: boolean = false
  editFunctionName: string | null = null;
  editFunctionArgs: any[] = [];

  setEditFunction(functionName: string, ...args: any[]) {
    this.editFunctionName = functionName;
    this.editFunctionArgs = args;
  }

  translateExpandedIndex: number = 0
  user: any;
  id: any = null;

  constructor(private fb: UntypedFormBuilder, public dialog: MatDialog, private crudService: ApiCrudService, private activatedRoute: ActivatedRoute, public router: Router, private schoolService: SchoolService,
    public courses: CoursesService
  ) {
    this.user = JSON.parse(localStorage.getItem('boukiiUser'));
    this.id = this.activatedRoute.snapshot.params.id;
    this.ModalFlux = +this.activatedRoute.snapshot.queryParamMap['params'].step || 0
  }
  detailData: any = { degrees: [], course_dates: [] }

  ngOnInit() {
    const extras = JSON.parse(JSON.parse(localStorage.getItem("boukiiUser")).schools[0].settings).extras
    this.extras = [...extras.food, ...extras.forfait, ...extras.transport]
    this.mode = this.id ? 'update' : 'create';
    forkJoin(
      this.mode === "update" ?
        {
          sports: this.getSports(),
          stations: this.getStations(),
          monitors: this.getMonitors(),
        } : {
          sports: this.getSports(),
          stations: this.getStations(),
        }).subscribe(({ sports, stations, monitors }) => {
          this.sportData = sports;
          this.stations = stations;
          this.courses.resetcourseFormGroup()
          if (this.mode === "create") {
            this.courses.courseFormGroup.patchValue({
              sport_id: this.sportData[0].sport_id,
              station_id: this.stations[0].id,
              duration: this.courses.duration[0],
              school_id: this.user.schools[0].id,
              hour_min: this.courses.hours[0],
              hour_max: this.courses.hours[4],
            })
            this.Confirm(0)
            setTimeout(() => this.loading = false, 0);
          } else {
            this.monitors = monitors
            this.crudService.get('/admin/courses/' + this.id,
              ['courseGroups.degree', 'courseGroups.courseDates.courseSubgroups.bookingUsers.client', 'sport'])
              .subscribe((data: any) => {
                this.detailData = data.data
                this.crudService.list('/stations', 1, 10000, 'desc', 'id', '&school_id=' + this.detailData.school_id)
                  .subscribe((st: any) => {
                    st.data.forEach((element: any) => {
                      if (element.id === this.detailData.station_id) this.detailData.station = element
                    });
                    //this.extras.push(...this.detailData.extras)
                    //this.crudService.list('/booking-users', 1, 10000, 'desc', 'id', '&school_id=' + this.detailData.school_id + '&course_id=' + this.detailData.id)
                    //  .subscribe((bookingUser) => {
                    //    this.detailData.users = [];
                    //    this.detailData.users = bookingUser.data;
                    //  })
                    this.courses.settcourseFormGroup(this.detailData)
                    this.getDegrees()
                    setTimeout(() => this.loading = false, 0);
                  })
              })
          }
          this.extrasFormGroup = this.fb.group({
            id: ["", Validators.required],
            product: ["", Validators.required],
            name: ["", Validators.required],
            price: [1, Validators.required],
            tva: [21, Validators.required],
            status: [true, Validators.required],
          })
          this.schoolService.getSchoolData().subscribe((data) => { this.schoolData = data.data })
        });
  }

  createExtras() {
    this.extrasModal = false;
    this.extrasFormGroup.patchValue({ id: "aFOR-" + this.extrasFormGroup.controls['name'].value + this.extrasFormGroup.controls['product'].value + this.extrasFormGroup.controls['price'].value })
    this.extras.push(this.extrasFormGroup.getRawValue());
    this.extrasFormGroup = this.fb.group({
      id: ["", Validators.required],
      product: ["", Validators.required],
      name: ["", Validators.required],
      price: [1, Validators.required],
      tva: [21, Validators.required],
      status: [true, Validators.required],
    })
  }

  getSportsType = () => this.crudService.list('/sport-types', 1, 1000).pipe(map(data => data.data));
  getMonitors = () => this.crudService.list('/monitors', 1, 10000, 'desc', 'id', '&school_id=' + this.user.schools[0].id).pipe(map(data => data.data));
  getSports = () => this.crudService.list('/school-sports', 1, 10000, 'desc', 'id', '&school_id=' + this.user.schools[0].id, null, null, null, ['sport']).pipe(map(sport => sport.data));

  getStations = () => this.crudService.list('/stations-schools', 1, 10000, 'desc', 'id', '&school_id=' + this.user.schools[0].id).pipe(
    map(station => station.data),
    mergeMap(stations => forkJoin(stations.map((element: any) => this.crudService.get('/stations/' + element.station_id).pipe(map(data => data.data)))))
  );

  getDegrees = () => this.crudService.list('/degrees', 1, 10000, 'asc', 'degree_order', '&school_id=' + this.courses.courseFormGroup.controls['school_id'].value + '&sport_id=' + this.courses.courseFormGroup.controls['sport_id'].value).subscribe((data) => {
    this.detailData.degrees = [];
    data.data.forEach((element: any) => {
      if (element.active) this.detailData.degrees.push({ ...element, }); //Subgrupo: this.getSubGroups(element.id)
    });
    const levelGrop = []
    this.detailData.degrees.forEach((level: any) => {
      level.active = false;
      this.detailData.course_dates.forEach((cs: any) => {
        cs.course_groups.forEach((group: any) => {
          if (group.degree_id === level.id) {
            level.active = true;
            level.old = true;
            level.age_min = group.age_min
            level.age_max = group.age_max
            level.max_participants = group.course_subgroups[0].max_participants
            level.course_subgroups = group.course_subgroups
          } level.visible = false;
        });
      });
      levelGrop.push({ ...level })
    });
    levelGrop.sort((a: any) => a.active ? -1 : 1)
    this.courses.courseFormGroup.patchValue({ levelGrop })
  });

  Confirm(add: number) {
    this.courses.courseFormGroup.markAsUntouched()
    this.ModalFlux += add
    if (this.ModalFlux === 1) {
      if (!this.courses.courseFormGroup.controls["course_type"].value) this.courses.courseFormGroup.patchValue({ course_type: 1 })
      this.courses.courseFormGroup.patchValue({
        icon: this.sportData.find((a: any) => a.sport_id === this.courses.courseFormGroup.controls['sport_id'].value).sport.icon_unselected
      })
      this.getDegrees();
    } else if (this.ModalFlux === 2) {
      if (
        this.courses.courseFormGroup.controls["name"].status === 'VALID' &&
        this.courses.courseFormGroup.controls["short_description"].status === 'VALID' &&
        this.courses.courseFormGroup.controls["description"].status === 'VALID' &&
        this.courses.courseFormGroup.controls["price"].status === 'VALID' &&
        this.courses.courseFormGroup.controls["max_participants"].status === 'VALID' &&
        (
          this.courses.courseFormGroup.controls['course_type'].value > 1 &&
          this.courses.courseFormGroup.controls["age_min"].status === 'VALID' &&
          this.courses.courseFormGroup.controls["age_max"].status === 'VALID' ||
          this.courses.courseFormGroup.controls['course_type'].value === 1
        )
      ) {
        if (this.mode === 'create') {

          setTimeout(async () => {
            const languages = ['fr', 'en', 'de', 'es', 'it'];
            const { name, short_description, description } = this.courses.courseFormGroup.controls;
            const translations: any = {};
            try {
              const translationPromises = languages.map(async (lang) => {
                const translatedName = await this.crudService.translateText(name.value, lang.toUpperCase()).toPromise();
                const translatedShortDescription = await this.crudService.translateText(short_description.value, lang.toUpperCase()).toPromise();
                const translatedDescription = await this.crudService.translateText(description.value, lang.toUpperCase()).toPromise();
                translations[lang] = {
                  name: translatedName?.data.translations[0].text,
                  short_description: translatedShortDescription?.data.translations[0].text,
                  description: translatedDescription?.data.translations[0].text,
                };
              });
              await Promise.all(translationPromises);
              this.courses.courseFormGroup.patchValue({ translations });
            } catch (error) {
              console.error("Error translating text:", error);
            }
          }, 1000);
        }
      } else {
        this.courses.courseFormGroup.markAllAsTouched()
        this.ModalFlux -= add
      }
    } else if (this.ModalFlux === 3) {
      if (
        this.courses.courseFormGroup.controls["date_start"].status === 'VALID' &&
        this.courses.courseFormGroup.controls["date_end"].status === 'VALID'
      ) {
      } else {
        this.courses.courseFormGroup.markAllAsTouched()
        this.ModalFlux -= add
      }
      if (this.courses.courseFormGroup.controls['course_type'].value === 2) {
        let Range = this.generarIntervalos(
          this.courses.courseFormGroup.controls["max_participants"].value,
          this.courses.duration.length,
          this.courses.duration
        )
        const settings = JSON.parse(this.user.schools[0].settings);
        for (const range in settings.prices_range.prices) Range[range] = { ...Range[range], ...settings.prices_range.prices[range] }
        this.courses.courseFormGroup.patchValue({ price_range: Range })
      }
    }
    else if (this.ModalFlux === 4) {
      if (this.courses.courseFormGroup.controls['course_type'].value === 1) {
        if (this.courses.courseFormGroup.controls['levelGrop'].value.some((item: any) => item.active)) {
        } else {
          this.ModalFlux -= add
        }
      } else if (this.courses.courseFormGroup.controls['course_type'].value === 2) {
      } else {
        const groups = this.courses.courseFormGroup.controls['settings'].value.groups;
        if (groups.every((group: any) => group.groupName && group.ageMin > 0 && group.ageMax > 0 && group.price > 0)) {
        } else {
          this.courses.courseFormGroup.controls['settings'].markAllAsTouched()
          this.ModalFlux -= add
        }
      }
    }
    else if (this.ModalFlux === 6) {
      this.ModalFlux--
      this.confirmModal = true
    }
  }

  find = (array: any[], key: string, value: string | boolean) => array.find((a: any) => value ? a[key] === value : a[key])
  filter = (array: any[], key: string, value: string | boolean) => array.filter((a: any) => value ? a[key] === value : a[key])

  selectLevel = (event: any, i: number) => {
    const levelGrop = this.courses.courseFormGroup.controls['levelGrop'].value
    const course_dates = this.courses.courseFormGroup.controls['course_dates'].value
    levelGrop[i].active = event.target.checked
    if (event.target.checked) {
      levelGrop[i].age_min = this.courses.courseFormGroup.controls['age_min'].value
      levelGrop[i].age_max = this.courses.courseFormGroup.controls['age_max'].value
      levelGrop[i].max_participants = this.courses.courseFormGroup.controls['max_participants'].value
      for (const course of course_dates) {
        if (this.mode === "create") {
          course.course_groups = [...course.course_groups, { ...levelGrop[i], degree_id: levelGrop[i].id, course_subgroups: [] }]
          course.groups = [...course.groups, { ...levelGrop[i], degree_id: levelGrop[i].id, subgroups: [] }]
        }
        else {
          course.course_groups = [...course.course_groups, { ...levelGrop[i], degree_id: levelGrop[i].id, course_id: this.courses.courseFormGroup.controls['id'].value, course_subgroups: [] }]
        }
      }
    } else {
      for (const course of course_dates) {
        course.course_groups = course.course_groups.filter((a: any) => a.id !== levelGrop[i].id)
        if (this.mode === "create") course.groups = course.groups.filter((a: any) => a.id !== levelGrop[i].id)
      }
    }
    this.courses.courseFormGroup.patchValue({ levelGrop, course_dates })
    if (event.target.checked) this.addLevelSubgroup(levelGrop[i], 0, true)
  }

  addLevelSubgroup = (level: any, j: number, add: boolean) => {
    const course_dates = this.courses.courseFormGroup.controls['course_dates'].value
    for (const course of course_dates) {
      for (const group in course.course_groups) {
        if (course.course_groups[group].degree_id === level.id) {
          if (add) {
            if (this.mode === "create") {
              course.groups[group].subgroups = [...course.groups[group].subgroups, { degree_id: level.id, max_participants: level.max_participants, monitor: null, monitor_id: null }]
            }
            course.course_groups[group].course_subgroups = [...course.course_groups[group].course_subgroups, { degree_id: level.id, max_participants: level.max_participants, monitor: null, monitor_id: null }]
          } else {
            if (this.mode === "create") {
              course.groups[group].subgroups.splice(j, 1)
            }
            course.course_groups[group].course_subgroups.splice(j, 1)
          }
        }
      }
    }
    this.courses.courseFormGroup.patchValue({ course_dates })
  }

  selectExtra = (event: any, item: any, i: number) => {
    if (this.courses.courseFormGroup.controls['course_type'].value === 3) {
      this.courses.courseFormGroup.controls['settings'].value.groups = JSON.parse(JSON.stringify(this.courses.courseFormGroup.controls['settings'].value.groups))
      if (event.checked || !this.courses.courseFormGroup.controls['settings'].value.groups[i].extras.find((a: any) => a.id === item.id)) this.courses.courseFormGroup.controls['settings'].value.groups[i].extras.push(item)
      else this.courses.courseFormGroup.controls['settings'].value.groups[i].extras = this.courses.courseFormGroup.controls['settings'].value.groups[i].extras.filter((a: any) => a.id !== item.id)
    } else {
      const extras = this.courses.courseFormGroup.controls['extras'].value
      if (event.checked || !extras.find((a: any) => a.id === item.id)) this.courses.courseFormGroup.patchValue({ extras: [...extras, item] })
      else this.courses.courseFormGroup.patchValue({ extras: extras.filter((a: any) => a.id !== item.id) })
    }
  }

  selectWeek = (day: string, event: any) => {
    const settings = this.courses.courseFormGroup.controls['settings'].value
    if (day === "0") settings.weekDays = { monday: event.checked, tuesday: event.checked, wednesday: event.checked, thursday: event.checked, friday: event.checked, saturday: event.checked, sunday: event.checked }
    else settings.weekDays[day] = event.checked
    this.courses.courseFormGroup.patchValue({ settings: settings })
  }

  endCourse() {
    const courseFormGroup = this.courses.courseFormGroup.getRawValue()
    courseFormGroup.translations = JSON.stringify(this.courses.courseFormGroup.controls['translations'].value)
    courseFormGroup.course_type === 1 ? delete courseFormGroup.settings : courseFormGroup.settings = JSON.stringify(this.courses.courseFormGroup.controls['settings'].value)
    if (this.mode === "create") {
      this.crudService.create('/admin/courses', courseFormGroup).subscribe((data) => {
        if (data.success) this.router.navigate(["/courses/detail/" + data.data.id])
      })
    } else {
      this.crudService.update('/admin/courses', courseFormGroup, this.id).subscribe((data) => {
        if (data.success) this.router.navigate(["/courses/detail/" + data.data.id])
      })
    }
  }

  getNumberArray = (num: number): any[] => ['intervalo', ...Array.from({ length: num }, (_, i) => `${i + 1}`)];;

  generarIntervalos = (personas: number, intervalo: number, duracion: string[]): any[] => {
    const resultado = [];
    for (let i = 0; i < intervalo; i++) {
      const obj = { intervalo: duracion[i] };
      for (let j = 1; j <= personas; j++) obj[j] = null
      resultado.push(obj);
    } return resultado;
  }

  addCategory = () => this.courses.courseFormGroup.controls['settings'].value.groups.push({ ...this.courses.default_activity_groups })

  addCourseDate = () => {
    const course_date = this.courses.courseFormGroup.controls['course_dates'].value
    const data = JSON.parse(JSON.stringify(course_date[course_date.length - 1]))
    delete data.id
    const newDate = new Date(course_date[course_date.length - 1].date);
    newDate.setDate(newDate.getDate() + 1);
    course_date.push({ ...data, date: newDate })
    this.courses.courseFormGroup.patchValue({ course_dates: course_date })
  }

  monitorSelect(event: any, level: any, j: number) {
    let course_dates = this.courses.courseFormGroup.controls['course_dates'].value
    course_dates[event.i].course_groups[course_dates[event.i].course_groups.findIndex((a: any) => a.degree_id === level.id)].course_subgroups[j].monitor = event.monitor
    course_dates[event.i].course_groups[course_dates[event.i].course_groups.findIndex((a: any) => a.degree_id === level.id)].course_subgroups[j].monitor_id = event.monitor.id
    this.courses.courseFormGroup.patchValue({ course_dates })
  }
  deleteCourseDate(i: number) {
    this.courses.courseFormGroup.controls['course_dates'].value.splice(i, 1)
  }
}
