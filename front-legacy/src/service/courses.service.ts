
import { Injectable } from '@angular/core';
import moment from 'moment';
import { TranslateService } from '@ngx-translate/core';
import { UntypedFormBuilder, UntypedFormGroup, Validators } from '@angular/forms';

@Injectable({ providedIn: 'root' })

export class CoursesService {
  constructor(private translateService: TranslateService, private fb: UntypedFormBuilder) { }
  courseFormGroup: UntypedFormGroup;
  nowDate = new Date(new Date().setHours(-new Date().getTimezoneOffset() / 60, 0, 0, 0));
  minDate = this.nowDate;
  maxDate = new Date(2099, 12, 31);

  settcourseFormGroup(data: any) {
    this.resetcourseFormGroup()
    this.courseFormGroup.patchValue({
      ...data,
      user: data.user ? data.user.username + " (" + data.user.first_name + " " + data.user.last_name + ")" : "",
      translations: JSON.parse(data.translations),
      icon: data.sport.icon_unselected,
      levelGrop: data.degrees,
      settings: JSON.parse(data.settings),
      discounts: data.discounts
    })
  }
  user: any = JSON.parse(localStorage.getItem('boukiiUser'))

  resetcourseFormGroup() {
    const settings = JSON.parse(JSON.parse(localStorage.getItem('boukiiUser')).schools[0].settings);
    this.courseFormGroup = this.fb.group({
      id: [null, Validators.required],
      sport_id: [null, Validators.required],
      is_flexible: [false, Validators.required],
      created_at: [new Date()],
      user: [this.user.username + " (" + this.user.first_name + " " + this.user.last_name + ")"],
      user_id: [this.user.id],
      booking_users: [[]],
      course_type: [null, Validators.required],
      name: ["Curso prueba " + new Date().toISOString(), Validators.required],
      short_description: ["Short Description Test", Validators.required],
      description: ["Description Test", Validators.required],
      price: [100, [Validators.required, Validators.min(1)]],
      currency: [settings?.taxes?.currency || 'CHF'],
      max_participants: [10, [Validators.required, Validators.min(1)]],
      image: ["", Validators.required],
      icon: ["", Validators.required],
      age_max: [99, [Validators.required, Validators.min(0), Validators.max(99)]],
      age_min: [0, [Validators.required, Validators.min(0), Validators.max(99)]],
      date_start: [this.nowDate, Validators.required],
      date_end: [this.nowDate, Validators.required],
      date_start_res: [this.nowDate],
      date_end_res: [this.nowDate],
      duration: [, Validators.required],
      confirm_attendance: [false],
      active: [true],
      online: [true],
      options: [true],
      translations: [
        {
          es: { name: '', short_description: '', description: '' },
          en: { name: '', short_description: '', description: '' },
          fr: { name: '', short_description: '', description: '' },
          it: { name: '', short_description: '', description: '' },
          de: { name: '', short_description: '', description: '' },
        }
      ],
      school_id: [this.user.schools[0].id],
      station_id: [null],
      course_dates: [[{ ...this.default_course_dates }], Validators.required],
      discounts: [[], Validators.required],
      unique: [true],
      hour_min: [],
      hour_max: [],
      price_range: [[]],
      extras: [[], Validators.required],
      levelGrop: [[], Validators.required],
      settings: [
        {
          weekDays: { monday: false, tuesday: false, wednesday: false, thursday: false, friday: false, saturday: false, sunday: false },
          periods: [],
          groups: [{ ...this.default_activity_groups }]
        }
      ],
    });
  }

  hours: string[] = [
    '08:00', '08:15', '08:30', '08:45', '09:00', '09:15', '09:30', '09:45',
    '10:00', '10:15', '10:30', '10:45', '11:00', '11:15', '11:30', '11:45',
    '12:00', '12:15', '12:30', '12:45', '13:00', '13:15', '13:30', '13:45',
    '14:00', '14:15', '14:30', '14:45', '15:00', '15:15', '15:30', '15:45',
    '16:00', '16:15', '16:30', '16:45', '17:00', '17:15', '17:30', '17:45', '18:00',
  ];

  hoursAll: string[] = [
    '08:00', '08:15', '08:30', '08:45', '09:00', '09:15', '09:30', '09:45',
    '10:00', '10:15', '10:30', '10:45', '11:00', '11:15', '11:30', '11:45',
    '12:00', '12:15', '12:30', '12:45', '13:00', '13:15', '13:30', '13:45',
    '14:00', '14:15', '14:30', '14:45', '15:00', '15:15', '15:30', '15:45',
    '16:00', '16:15', '16:30', '16:45', '17:00', '17:15', '17:30', '17:45',
    '18:00', '18:15', '18:30', '18:45', '19:00', '19:15', '19:30', '19:45',
    '20:00', '20:15', '20:30', '20:45', '21:00', '21:15', '21:30', '21:45',
    '22:00', '22:15', '22:30', '22:45', '23:00', '23:15', '23:30', '23:45',
    '24:00',
  ];

  duration: string[] = [
    '15min', '30min', '45min', '1h', '1h 15min', '1h 30min', '1h 45min',
    '2h', '2h 15min', '2h 30min', '2h 45min', '3h', '3h 15min', '3h 30min', '3h 45min',
    '4h', '4h 15min', '4h 30min', '4h 45min', '5h', '5h 15min', '5h 30min', '5h 45min',
    '6h',
    //'6h 15min', '6h 30min', '6h 45min', '7h', '7h 15min', '7h 30min', '7h 45min'
  ];

  ndays: number[] = [2, 3, 4, 5, 6, 7, 8, 9, 10];

  weekSelect: string[] = ["monday", "tuesday", "wednesday", "thursday", "friday", "saturday", "sunday"]

  default_course_dates =
    {
      date: this.nowDate.toISOString(),
      hour_start: this.hours[0],
      duration: this.duration[0],
      date_end: this.nowDate.toISOString(),
      hour_end: this.hours[1],
      course_groups: [],
      groups: []
    }

  default_activity_groups: { groupName: string, ageMin: number, ageMax: number, optionName: string, price: number, extras: any[] } =
    { groupName: "", ageMin: 18, ageMax: 99, optionName: "", price: 1, extras: [] }


  getCourseName(course: any) {
    if (!course.translations) return course.name;
    else {
      const translations = JSON.parse(course.translations);
      return translations[this.translateService.currentLang].name !== null && translations[this.translateService.currentLang].name !== '' ? translations[this.translateService.currentLang].name : course.name;
    }
  }

  getShortDescription(course: any) {
    if (!course.translations) return course.short_description;
    else {
      const translations = JSON.parse(course.translations);
      return translations[this.translateService.currentLang].short_description !== null && translations[this.translateService.currentLang].short_description !== '' ? translations[this.translateService.currentLang].short_description : course.short_description;
    }
  }

  getDescription(course: any) {
    if (!course.translations) return course.description;
    else {
      const translations = JSON.parse(course.translations);
      return translations[this.translateService.currentLang].description !== null && translations[this.translateService.currentLang].description !== '' ? translations[this.translateService.currentLang].description : course.description;
    }
  }

  getShortestDuration(times: any) {
    let shortest = null;
    times.forEach((time: any) => {
      const start = moment(time.hour_start, "HH:mm:ss");
      const end = moment(time.hour_end, "HH:mm:ss");
      const duration = moment.duration(end.diff(start));
      if (shortest === null || duration < shortest) shortest = duration;
    });

    if (shortest !== null) {
      const hours = shortest.hours();
      const minutes = shortest.minutes();
      return `${hours > 0 ? hours + 'h ' : ''}${minutes > 0 ? minutes + 'min' : ''}`.trim();
    } else return "No_durations_found";
  }

  getAgeRange(data: any[]): { age_min: number, age_max: number } {
    let age_min = Number.MAX_SAFE_INTEGER;
    let age_max = Number.MIN_SAFE_INTEGER;
    data.forEach(item => {
      if (item.age_min < age_min) age_min = item.age_min;
      if (item.age_max > age_max) age_max = item.age_max;
    });
    return { age_min, age_max };
  }

  findFirstCombinationWithValues(data: any) {
    if (data !== null) {
      for (const intervalo of data) {
        if (Object.keys(intervalo).some(key => key !== 'intervalo' && intervalo[key] !== null)) return intervalo;
      } return null;
    }
  }

  encontrarPrimeraClaveConValor(obj: any): string | null {
    if (obj !== null) {
      for (const clave of Object.keys(obj)) {
        if (obj[clave] !== null && clave !== 'intervalo') return obj[clave];
      } return null;
    }
  }

  weekString = (): string => {
    const allDays = Object.keys(this.courseFormGroup.controls['settings'].value.weekDays).filter(day => this.courseFormGroup.controls['settings'].value.weekDays[day]);
    const workDays = ["monday", "tuesday", "wednesday", "thursday", "friday"];
    const weekendDays = ["saturday", "sunday"];
    const isAllTrue = allDays.length === 7;
    const isAllWorkDaysTrue = workDays.every(day => this.courseFormGroup.controls['settings'].value.weekDays[day]) && weekendDays.every(day => !this.courseFormGroup.controls['settings'].value.weekDays[day]);
    const isAllFalse = allDays.length === 0;
    if (isAllTrue) return this.translateService.instant("all_days_week");
    if (isAllWorkDaysTrue) return this.translateService.instant("workdays_only");
    if (isAllFalse) return null;
    const translatedDays = allDays.map(day => this.translateService.instant(day));
    const lastDay = translatedDays.pop();
    if (translatedDays.length === 0) return lastDay;
    return `${translatedDays.join(", ")} y ${lastDay}`;
  }

  addMinutesToTime(timeString: string, minutesToAdd: string) {
    const [hours, minutes] = timeString.split(":").map(Number);
    const date = new Date();
    date.setHours(hours);
    date.setMinutes(minutes + ((this.duration.findIndex((value) => value == minutesToAdd) + 1) * 15));
    const newHours = String(date.getHours()).padStart(2, "0");
    const newMinutes = String(date.getMinutes()).padStart(2, "0");
    return `${newHours}:${newMinutes}`;
  }

}

