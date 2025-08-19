import { ChangeDetectorRef, Component, OnInit } from '@angular/core';
import { FormControl, UntypedFormBuilder, UntypedFormGroup, Validators } from '@angular/forms';
import { MatSnackBar } from '@angular/material/snack-bar';
import { ActivatedRoute, Router } from '@angular/router';
import { TranslateService } from '@ngx-translate/core';
import { Observable, map, startWith } from 'rxjs';
import { fadeInUp400ms } from 'src/@vex/animations/fade-in-up.animation';
import { stagger20ms } from 'src/@vex/animations/stagger.animation';
import { MOCK_COUNTRIES } from 'src/app/static-data/countries-data';
import { MOCK_LANGS } from 'src/app/static-data/language-data';
import { MOCK_PROVINCES } from 'src/app/static-data/province-data';
import { ApiCrudService } from 'src/service/crud.service';

@Component({
  selector: 'vex-admin-create-update',
  templateUrl: './admin-create-update.component.html',
  styleUrls: ['./admin-create-update.component.scss'],
  animations: [stagger20ms, fadeInUp400ms]
})
export class AdminCreateUpdateComponent implements OnInit {

  displayedColumns: string[] = ['name', 'date'];

  formInfoAccount: UntypedFormGroup;
  formPersonalInfo: UntypedFormGroup;
  myControlCountries = new FormControl();
  myControlProvinces = new FormControl();
  levelForm = new FormControl();

  filteredCountries: Observable<any[]>;
  filteredProvinces: Observable<any[]>;

  languagesControl = new FormControl([]);
  languages = [];
  filteredLanguages: Observable<any[]>;
  selectedLanguages = [];

  loading = true;

  today: Date;
  minDate: Date;

  mockCountriesData = MOCK_COUNTRIES;
  mockProvincesData = MOCK_PROVINCES;

  maxSelection = 6;

  defaults = {
    username: null,
    first_name: null,
    last_name: null,
    email: null,
    image: null,
    type: "1",
    active: true,
    password: null
  }


  id: any = null;

  user: any;
  mode: 'create' | 'update' = 'create';

  constructor(private fb: UntypedFormBuilder, private cdr: ChangeDetectorRef, private snackbar: MatSnackBar, private router: Router, private crudService: ApiCrudService,
    private activatedRoute: ActivatedRoute, private translateService: TranslateService) {
    this.today = new Date();
    this.minDate = new Date(this.today);
    this.minDate.setFullYear(this.today.getFullYear() - 18);
  }

  ngOnInit(): void {

    this.user = JSON.parse(localStorage.getItem('boukiiUser'));
    this.id = this.activatedRoute.snapshot.params.id;

    if (!this.id || this.id === null) {
      this.mode = 'create';
      this.formInfoAccount = this.fb.group({
        name: ['', Validators.required],
        surname: ['', Validators.required],
        email: ['', [Validators.required, Validators.email]],
        username: [''],
        password: ['', Validators.required],

      });

      this.formPersonalInfo = this.fb.group({
        fromDate: [''],
        phone: [''],
        mobile: ['', Validators.required],
        address: [''],
        postalCode: [''],
        country: this.myControlCountries,
        province: this.myControlProvinces

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

      this.filteredLanguages = this.languagesControl.valueChanges.pipe(
        startWith(''),
        map(language => (language ? this._filterLanguages(language) : this.languages.slice()))
      );

      this.getLanguages();
    } else {
      this.mode = 'update';
      this.crudService.get('/users/'+this.id)
        .subscribe((data) => {
          this.defaults = data.data;

          this.formInfoAccount = this.fb.group({
            name: ['', Validators.required],
            surname: ['', Validators.required],
            email: ['', [Validators.required, Validators.email]],
            username: [''],
            password: [''],

          });

          this.formPersonalInfo = this.fb.group({
            fromDate: [''],
            phone: [''],
            mobile: ['', Validators.required],
            address: [''],
            postalCode: [''],
            country: this.myControlCountries,
            province: this.myControlProvinces

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

          this.filteredLanguages = this.languagesControl.valueChanges.pipe(
            startWith(''),
            map(language => (language ? this._filterLanguages(language) : this.languages.slice()))
          );

          this.getLanguages();
        })
    }


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

  toggleSelectionLanguages(language: any): void {
    if (this.selectedLanguages.length < this.maxSelection) {

      const index = this.selectedLanguages.findIndex(l => l.code === language.code);
      if (index >= 0) {
        this.selectedLanguages.splice(index, 1);
      } else {
        this.selectedLanguages.push({ id: language.id, name: language.name, code: language.code });
      }
    } else {
      this.snackbar.open(this.translateService.instant('snackbar.admin.langs'), 'OK', {duration: 3000});
    }
  }

  getSelectedLanguageNames(): string {
    return this.selectedLanguages.map(language => language.name).join(', ');
  }

  getLanguages() {
    this.crudService.list('/languages', 1, 1000)
      .subscribe((data) => {
        this.languages = data.data.reverse();
        this.loading = false;

      })
  }



  save() {

    if (this.mode === 'create') {
      this.create();
    } else if (this.mode === 'update') {
      this.update();
    }
  }

  create() {


    this.crudService.create('/users', this.defaults)
      .subscribe((user) => {
        this.crudService.create('/school-users', {user_id: user.data.id, school_id: this.user.schools[0].id})
          .subscribe(() => {

            this.snackbar.open(this.translateService.instant('snackbar.admin.create'), 'OK', {duration: 3000});
            this.router.navigate(['/admins']);
          })
      })
  }

  update() {

    this.crudService.update('/users', this.defaults, this.id)
      .subscribe((user) => {
        this.snackbar.open(this.translateService.instant('snackbar.admin.update'), 'OK', {duration: 3000});
        this.router.navigate(['/admins']);
      })
  }
}

