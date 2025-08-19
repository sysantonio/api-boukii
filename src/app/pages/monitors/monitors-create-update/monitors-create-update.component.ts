import { ChangeDetectorRef, Component, OnInit, ViewChild } from '@angular/core';
import { FormControl, UntypedFormBuilder, UntypedFormGroup, Validators } from '@angular/forms';
import { MatSnackBar } from '@angular/material/snack-bar';
import { MatStepper } from '@angular/material/stepper';
import { MatTable, _MatTableDataSource } from '@angular/material/table';
import { Router } from '@angular/router';
import { TranslateService } from '@ngx-translate/core';
import { Observable, map, startWith } from 'rxjs';
import { fadeInUp400ms } from 'src/@vex/animations/fade-in-up.animation';
import { stagger20ms } from 'src/@vex/animations/stagger.animation';
import { MOCK_COUNTRIES } from 'src/app/static-data/countries-data';
import { LEVELS } from 'src/app/static-data/level-data';
import { MOCK_PROVINCES } from 'src/app/static-data/province-data';
import { ApiCrudService } from 'src/service/crud.service';

@Component({
  selector: 'vex-monitors-create-update',
  templateUrl: './monitors-create-update.component.html',
  styleUrls: ['./monitors-create-update.component.scss'],
  animations: [fadeInUp400ms, stagger20ms]
})
export class MonitorsCreateUpdateComponent implements OnInit {

  @ViewChild('childrenTable') table: MatTable<any>;
  mode: 'create' | 'update' = 'create';

  displayedColumns: string[] = ['name', 'level', 'salary', 'auth'];
  displayedColumnsChildren: string[] = ['name', 'date'];

  imagePreviewUrl: string | ArrayBuffer;
  formInfoAccount: UntypedFormGroup;
  formPersonalInfo: UntypedFormGroup;
  formWorkInfo: UntypedFormGroup;
  formCivilStatusInfo: UntypedFormGroup;
  formSportInfo: UntypedFormGroup;
  myControlStations = new FormControl();
  myControlCountries = new FormControl();
  myControlWorkCountries = new FormControl();
  myControlProvinces = new FormControl();
  myControlCivilStatus = new FormControl();
  levelForm = new FormControl();
  salaryForm = new FormControl();
  languagesControl = new FormControl([]);

  filteredStations: Observable<any[]>;
  filteredCountries: Observable<any[]>;
  filteredWorkCountries: Observable<any[]>;
  filteredProvinces: Observable<any[]>;
  filteredCivilStatus: Observable<string[]>;
  filteredLevel: Observable<any[]>;
  filteredSalary: Observable<any[]>;
  filteredLanguages: Observable<any[]>;

  sportsControl = new FormControl();
  selectedSports: any[] = [];
  filteredSports: Observable<any[]>;
  sportsData = new _MatTableDataSource([]);

  selectedLanguages = [];

  today: Date;
  minDate: Date;
  minDateChild: Date;
  maxSelection = 6;
  childrenData = new _MatTableDataSource([]);

  mockCivilStatus: string[] = ['Single', 'Mariée', 'Veuf', 'Divorcé'];
  mockCountriesData = MOCK_COUNTRIES;
  mockWorkCountriesData = MOCK_COUNTRIES;
  mockProvincesData = MOCK_PROVINCES;
  mockLevelData = LEVELS;
  languages = [];
  salaryData = [];
  authorisedLevels = [];

  defaults = {
    active_school: null,
    email: null,
    username: null,
    first_name: null,
    last_name: null,
    birth_date: null,
    phone: null,
    telephone: null,
    address: null,
    cp: null,
    city: null,
    province: null,
    country: null,
    image: null,
    avs: null,
    work_license: null,
    bank_details: null,
    children: null,
    civil_status: null,
    family_allowance: false,
    partner_work_license: null,
    partner_works: false,
    language1_id: null,
    language2_id: null,
    language3_id: null,
    language4_id: null,
    language5_id: null,
    language6_id: null,
    partner_percentaje: null,
    user_id: null,
    active_station: null,
    world_country: null
  }

  defaultsUser = {
    username: null,
    email: null,
    password: null,
    image: null,
    type: 'monitor',
    active: false,
  }

  loading: boolean = true;
  user: any;
  schoolSports: any[] = [];
  stations: any[] = [];

  constructor(private fb: UntypedFormBuilder, private cdr: ChangeDetectorRef, private crudService: ApiCrudService,
    private snackbar: MatSnackBar, private router: Router, private translateService: TranslateService) {
    this.today = new Date();
    this.minDate = new Date(this.today);
    this.minDateChild = new Date(this.today);
    this.minDate.setFullYear(this.today.getFullYear() - 13);
    this.minDateChild.setFullYear(this.today.getFullYear() - 0);
  }

  ngOnInit(): void {

    this.user = JSON.parse(localStorage.getItem('boukiiUser'));

    this.formInfoAccount = this.fb.group({
      image: [''],
      name: ['', Validators.required],
      surname: ['', Validators.required],
      email: ['', [Validators.required, Validators.email]],
      username: [''],
      station: [''],

    });

    this.formPersonalInfo = this.fb.group({
      fromDate: ['', Validators.required],
      phone: [''],
      mobile: ['', Validators.required],
      address: [''],
      postalCode: [''],
      country: this.myControlCountries,
      province: this.myControlProvinces

    });

    this.formWorkInfo = this.fb.group({
      avs: [],
      workId: [],
      iban: [],
      countryWork: this.myControlWorkCountries,
      children: [],
      childName: [],
      childAge: [],
      sports: [],
      sportName: []
    });

    this.formCivilStatusInfo = this.fb.group({

      civilStatus: [],
      spouse: ['No'],
      workMobility: ['No'],
      spouseWorkId: [],
      spousePercentage: []
    });

    this.filteredStations = this.myControlStations.valueChanges
      .pipe(
        startWith(''),
        map(value => this._filterStations(value))
      );

    this.myControlStations.valueChanges.subscribe(value => {
      this.formInfoAccount.get('station').setValue(value);
    });

    this.filteredCountries = this.myControlCountries.valueChanges.pipe(
      startWith(''),
      map(value => typeof value === 'string' ? value : value.name),
      map(name => name ? this._filterCountries(name) : this.mockCountriesData.slice())
    );

    this.myControlCountries.valueChanges.subscribe(country => {
      this.myControlProvinces.setValue('');  // Limpia la selección anterior de la provincia
      this.filteredProvinces = this._filterProvinces(country.id);
    });

    this.filteredWorkCountries = this.myControlWorkCountries.valueChanges.pipe(
      startWith(''),
      map(value => typeof value === 'string' ? value : value.name),
      map(name => name ? this._filterCountries(name) : this.mockWorkCountriesData.slice())
    );

    /*this.myControlWorkCountries.valueChanges.subscribe(country => {
      this.formWorkInfo.get('countryWork').setValue(country);
    });*/

    this.filteredCivilStatus = this.myControlCivilStatus.valueChanges.pipe(
      startWith(''),
      map(value => this._filterCivilStatus(value))
    );

    this.filteredSports = this.sportsControl.valueChanges.pipe(
      startWith(''),
      map((sport: string | null) => sport ? this._filterSports(sport) : this.schoolSports.slice())
    );


    this.filteredLevel = this.levelForm.valueChanges.pipe(
      startWith(''),
      map((value: any) => typeof value === 'string' ? value : value?.annotation),
      map(annotation => annotation ? this._filterLevel(annotation) : this.mockLevelData.slice())
    );


    this.filteredSalary = this.salaryForm.valueChanges.pipe(
      startWith(''),
      map((value: any) => typeof value === 'string' ? value : value?.annotation),
      map(annotation => annotation ? this._filterSalary(annotation) : this.salaryData.slice())
    );

    this.filteredLanguages = this.languagesControl.valueChanges.pipe(
      startWith(''),
      map(language => (language ? this._filterLanguages(language) : this.languages.slice()))
    );

    this.getSchoolSportDegrees();
    this.getStations();
    this.getLanguages();
    this.getSalarySchoolData();

    setTimeout(() => {
      this.getSports();
      this.loading = false;

    }, 500);
  }

  onFileChanged(event: Event) {
    const file = (event.target as HTMLInputElement).files[0];
    if (file) {
      const reader = new FileReader();

      reader.onload = () => {
        this.imagePreviewUrl = reader.result;
        this.defaults.image = reader.result;
      };

      reader.readAsDataURL(file);
    }
  }

  private _filterStations(value: string): string[] {
    const filterValue = value.toLowerCase();
    return this.stations.filter(option => option.name.toLowerCase().includes(filterValue));
  }

  passwordValidator(formControl: FormControl) {
    const { value } = formControl;
    const hasUpperCase = /[A-Z]/.test(value);
    const hasLowerCase = /[a-z]/.test(value);
    const hasNumeric = /[0-9]/.test(value);
    const hasSpecialChar = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(value);

    if (hasUpperCase && hasLowerCase && hasNumeric && hasSpecialChar) {
      return null;
    } else {
      return { passwordStrength: true };
    }
  }

  /**Countries */

  private _filterCountries(name: string): any[] {
    const filterValue = name.toLowerCase();
    return this.mockCountriesData.filter(country => country.name.toLowerCase().includes(filterValue));
  }

  private _filterProvinces(countryId: number): Observable<any[]> {
    return this.myControlProvinces.valueChanges.pipe(
      startWith(''),
      map(value => typeof value === 'string' ? value : value.name),
      map(name => name ? this._filter(name, countryId) : this.mockProvincesData.filter(p => p.country_id === countryId).slice())
    );
  }

  private _filter(name: string, countryId: number): any[] {
    const filterValue = name.toLowerCase();
    return this.mockProvincesData.filter(province => province.country_id === countryId && province.name.toLowerCase().includes(filterValue));
  }

  private _filterCivilStatus(value: string): string[] {
    const filterValue = value.toLowerCase();
    return this.mockCivilStatus.filter(civilStatus => civilStatus.toLowerCase().includes(filterValue));
  }

  private _filterSports(value: any): any[] {
    const filterValue = typeof value === 'string' ? value?.toLowerCase() : value?.name.toLowerCase();
    return this.schoolSports.filter(sport => sport?.name.toLowerCase().indexOf(filterValue) === 0);
  }

  private _filterLevel(name: string): any[] {
    const filterValue = name.toLowerCase();
    return this.mockLevelData.filter(level => level.annotation.toLowerCase().includes(filterValue));
  }

  private _filterSalary(name: string): any[] {
    const filterValue = name.toLowerCase();
    return this.salaryData.filter(level => level.annotation.toLowerCase().includes(filterValue));
  }

  private _filterLanguages(value: any): any[] {
    const filterValue = value.toLowerCase();
    return this.languages.filter(language => language?.name.toLowerCase().includes(filterValue));
  }

  displayFnCountry(country: any): string {
    return country && country.name ? country.name : '';
  }

  displayFnProvince(province: any): string {
    return province && province.name ? province.name : '';
  }

  displayFnLevel(level: any): string {
    return level && level.name ? level.name : '';
  }

  displayFnSalary(salary: any): string {
    return salary && salary.name ? salary.name : '';
  }

  removeChild(course: any) {

    let index = -1;

    this.childrenData.data.forEach((element, idx) => {
      if (course.annotation === element.annotation && course.name === element.name) {
        index = idx;
      }
    });
    if (index > -1) {
      this.childrenData.data.splice(index, 1);
      this.table.renderRows();

    }
    // Aquí también puedes deseleccionar el chip correspondiente
  }

  updateChildren(event: Event): void {
    const inputElement = event.target as HTMLInputElement;
    const num = parseInt(inputElement.value, 10);

    if (isNaN(num) || num < 0) {
      return;
    }

    const currentData = this.childrenData.data;
    const difference = num - currentData.length;

    if (difference > 0) {
      for (let i = 0; i < difference; i++) {
        currentData.push({ name: '', date: null });
      }
    } else if (difference < 0) {
      currentData.splice(num);
    }

    this.childrenData.data = [...currentData];
  }

  updateSelectedSports(selected: any[]) {
    this.selectedSports = selected.map(sport => ({
      sportName: sport.name,
      sportId: sport.id,
      level: null
    }));
  }

  toggleSelection(sport: any): void {
    const index = this.selectedSports.findIndex(s => s.sport_id === sport.sport_id);
    if (index >= 0) {
      this.selectedSports.splice(index, 1);
    } else {
      this.selectedSports.push(sport);
    }

    // Crear una nueva referencia para el array
    this.selectedSports = [...this.selectedSports];

    // Actualizar los datos de la tabla
    this.sportsData.data = this.selectedSports;

    // Detectar cambios manualmente para asegurarse de que Angular reconozca los cambios
    this.cdr.detectChanges();

  }

  getSelectedSportsNames(): string {
    return this.sportsControl.value?.map(sport => sport.name).join(', ') || '';
  }

  toggleSelectionLanguages(language: any): void {
    if (this.selectedLanguages.length < this.maxSelection) {

      const index = this.selectedLanguages.findIndex(l => l.code === language.code);
      if (index >= 0) {
        this.selectedLanguages.splice(index, 1);
      } else {
        this.selectedLanguages.push({ id: language.id, name: language.name, code: language.code });
      }
    } else {
      this.snackbar.open(this.translateService.instant('snackbar.admin.langs'), 'OK', { duration: 3000 });
    }
  }


  getSelectedLanguageNames(): string {
    return this.selectedLanguages.map(language => language.name).join(', ');
  }

  getLanguages() {
    this.crudService.list('/languages', 1, 1000)
      .subscribe((data) => {
        this.languages = data.data.reverse();
      })
  }

  getSports() {
    this.crudService.list('/sports', 1, 10000, 'desc', 'id', '&school_id=' + this.user.schools[0].id)
      .subscribe((data) => {
        data.data.forEach(element => {
          this.schoolSports.forEach(sport => {
            if (element.id === sport.sport_id) {
              sport.name = element.name;
            }
          });
        });
      })
  }

  save() {

    if (this.mode === 'create') {
      this.create();
    }
  }

  create() {
    this.loading = true;
    this.defaultsUser.email = this.defaults.email;
    this.defaultsUser.image = this.imagePreviewUrl;
    this.defaults.image = this.imagePreviewUrl;
    this.defaults.active_school = this.user.schools[0].id;
    this.setLanguages();

    this.crudService.create('/users', this.defaultsUser)
      .subscribe((user) => {
        this.defaults.user_id = user.data.id;

        this.crudService.create('/monitors', this.defaults)
          .subscribe((monitor) => {
            this.snackbar.open(this.translateService.instant('snackbar.monitor.create'), 'OK', { duration: 3000 });

            const schoolRel = {
              monitor_id: monitor.data.id,
              school_id: this.user.schools[0].id,
              station_id: this.defaults.active_station,
              active_school: this.defaultsUser.active
            }

            this.crudService.create('/monitors-schools', schoolRel)
              .subscribe((a) => { })

            this.crudService.create('/monitors-schools', { monitor_id: monitor.data.id, school_id: this.user.schools[0].id })
              .subscribe((monitorSchool) => {
                this.sportsData.data.forEach(element => {
                  this.crudService.create('/monitor-sports-degrees', {
                    is_default: false, monitor_id: monitor.data.id, sport_id: element.sport_id,
                    school_id: this.user.schools[0].id, degree_id: element.level.id, salary_level: element.salary_id, allow_adults: element.allowAdults ? element.allowAdults : false
                  })
                    .subscribe((e) => {
                      this.authorisedLevels.forEach(auLevel => {

                        if (e.data.sport_id === auLevel.sport_id) {

                          this.crudService.create('/monitor-sport-authorized-degrees', { monitor_sport_id: e.data.id, degree_id: auLevel.id })
                            .subscribe((d) => { })
                        }
                      });
                    })
                });
                setTimeout(() => {
                  this.router.navigate(['/monitors']);

                }, 1200);
              })
          })
      })
  }

  getStations() {
    this.crudService.list('/stations-schools', 1, 10000, 'desc', 'id', '&school_id=' + this.user.schools[0].id)
      .subscribe((station) => {
        station.data.forEach(element => {
          this.crudService.get('/stations/' + element.station_id)
            .subscribe((data) => {
              this.stations.push(data.data);

            })
        });
      })
  }

  getSchoolSportDegrees() {
    this.crudService.list('/school-sports', 1, 10000, 'desc', 'id', '&school_id=' + this.user.schools[0].id)
      .subscribe((sport) => {
        this.schoolSports = sport.data;
        sport.data.forEach((element, idx) => {
          this.crudService.list('/degrees', 1, 10000, 'asc', 'degree_order', '&school_id=' + this.user.schools[0].id + '&sport_id=' + element.sport_id)
            .subscribe((data) => {
              this.schoolSports[idx].degrees = data.data;
            });
        });
      })
  }

  setLanguages() {
    if (this.selectedLanguages.length >= 1) {

      this.defaults.language1_id = this.selectedLanguages[0].id;
    } if (this.selectedLanguages.length >= 2) {

      this.defaults.language2_id = this.selectedLanguages[1].id;
    } if (this.selectedLanguages.length >= 3) {

      this.defaults.language3_id = this.selectedLanguages[2].id;
    } if (this.selectedLanguages.length >= 4) {

      this.defaults.language4_id = this.selectedLanguages[3].id;
    } if (this.selectedLanguages.length >= 5) {

      this.defaults.language5_id = this.selectedLanguages[4].id;
    } if (this.selectedLanguages.length === 6) {

      this.defaults.language6_id = this.selectedLanguages[5].id;
    }
  }

  goTo(route: string) {
    this.router.navigate([route]);
  }

  getSalarySchoolData() {
    this.crudService.list('/school-salary-levels', 1, 10000, 'desc', 'pay', '&school_id=' + this.user.schools[0].id)
      .subscribe((data) => {
        this.salaryData = data.data;
      })
  }

  authoriseLevel(level) {

    const index = this.authorisedLevels.find(l => l.id === level.id && l.sport_id === level.sport_id);
    if (index) {
      this.authorisedLevels.splice(index, 1);
    } else {
      this.authorisedLevels.push(level);
    }
  }

  isAuthorized(level: any) {
    return this.authorisedLevels.find(l => l.id === level.id && l.sport_id === level.sport_id);
  }

  setValueSpouse(value: any) {

    this.defaults.partner_works = value.value === 'y';
    this.cdr.detectChanges();
  }

  setValueLocation(value: any) {

    this.defaults.family_allowance = value.value === 'y';
    this.cdr.detectChanges();

  }

  goToStep3(stepper: MatStepper) {
    if (this.selectedLanguages.length === 0) {
      this.snackbar.open(this.translateService.instant('snackbar.client.mandatory_language'), 'OK', { duration: 3000 });
      return;
    }

    stepper.next();
  }

  goToStep5(stepper: MatStepper) {
    if (this.selectedSports.length === 0) {
      this.snackbar.open(this.translateService.instant('snackbar.client.sport_language'), 'OK', { duration: 3000 });
      return;
    }

    stepper.next();
  }
}
