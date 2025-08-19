import { Component, OnInit } from '@angular/core';
import { FormControl, UntypedFormBuilder, UntypedFormGroup, Validators } from '@angular/forms';
import { fadeInUp400ms } from 'src/@vex/animations/fade-in-up.animation';
import { stagger20ms } from 'src/@vex/animations/stagger.animation';
import { Observable, map, of, startWith } from 'rxjs';
import { ApiCrudService } from 'src/service/crud.service';
import { MatSnackBar } from '@angular/material/snack-bar';
import { ActivatedRoute, Router } from '@angular/router';
import { TranslateService } from '@ngx-translate/core';

@Component({
  selector: 'vex-discounts-create-update',
  templateUrl: './discounts-create-update.component.html',
  styleUrls: ['./discounts-create-update.component.scss'],
  animations: [fadeInUp400ms, stagger20ms]
})
export class DiscountsCreateUpdateComponent implements OnInit {

  mode: 'create' | 'update' = 'create';
  defaults: any = {
    code: null,
    quantity: null,
    remaining_balance: null,
    payed: false,
    client_id: null,
    school_id: null,
  };
  logs: any = [];
  user: any;

  loading: boolean = true;
  form: UntypedFormGroup;
  clientsForm = new FormControl('');
  filteredOptions: Observable<any[]>;

  clients = [];
  id: any = null;

  constructor(private fb: UntypedFormBuilder, private crudService: ApiCrudService, private translateService: TranslateService,
              private snackbar: MatSnackBar, private router: Router, private activatedRoute: ActivatedRoute) {

    this.user = JSON.parse(localStorage.getItem('boukiiUser'));
    this.form = this.fb.group({
      code:[null, Validators.required],
      quantity:[null],
      percentage:[null],
      remaining:[null],
      total:[null],
    });
  }

  ngOnInit() {
    this.id = this.activatedRoute.snapshot.params.id;

    if (!this.id || this.id === null) {
      this.mode = 'create';
      this.loading = false;
    } else {
      this.mode = 'update';
      this.getVoucher();
    }
  }

  save() {

    if (this.mode === 'create') {
      this.create();
    } else if (this.mode === 'update') {
      this.update();
    }
  }

  create() {

    const data = {
      code: this.defaults.code === null ? "BOU-"+this.generateRandomNumber() : this.defaults.code,
      quantity: this.defaults.quantity,
      percentage: this.defaults.percentage,
      remaining: this.defaults.total,
      total: this.defaults.total,
      school_id: this.user.schools[0].id
    };

    this.crudService.create('/discount-codes', data)
      .subscribe((res) => {
        this.snackbar.open(this.translateService.instant('snackbar.bonus.create'), 'OK', {duration: 3000});
        this.router.navigate(['/discount-codes'])
      })
  }

  update() {

    const data = {
      code: this.defaults.code,
      quantity: this.defaults.quantity,
      percentage: this.defaults.percentage,
      remaining: this.defaults.remaining,
      total: this.defaults.total,
      school_id: this.user.schools[0].id
    };

    if(this.defaults.remaining > this.defaults.total) {
      this.snackbar.open(this.translateService.instant('error.max_quantity'), 'OK', {duration: 3000});
    } else {

      this.crudService.update('/discount-codes', data, this.id)
        .subscribe((res) => {
          this.snackbar.open(this.translateService.instant('snackbar.bonus.update'), 'OK', {duration: 3000});
          this.router.navigate(['/discount-codes'])
        })
    }
  }

  generateRandomCode() {
    this.defaults.code = "BOU-"+this.generateRandomNumber();
  }


  displayFn(client: any): string {
    return client && client?.first_name && client?.last_name ? client?.first_name + ' ' + client?.last_name : client?.first_name;
  }


  getVoucher() {
    this.crudService.get('/discount-codes/'+this.id)
      .subscribe((data: any) => {
        this.defaults = data.data;
        this.loading = false;
      })
  }


  generateRandomNumber() {
    const min = 10000000; // límite inferior para un número de 5 cifras
    const max = 99999999; // límite superior para un número de 5 cifras
    return Math.floor(Math.random() * (max - min + 1)) + min;
  }

  goTo(route: string) {
    this.router.navigate([route]);
  }
}
