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
  selector: 'vex-bonuses-create-update',
  templateUrl: './bonuses-create-update.component.html',
  styleUrls: ['./bonuses-create-update.component.scss'],
  animations: [fadeInUp400ms, stagger20ms]
})
export class BonusesCreateUpdateComponent implements OnInit {

  mode: 'create' | 'update' = 'create';
  defaults: any = {
    code: null,
    quantity: null,
    remaining_balance: null,
    payed: false,
    is_gift: false,
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
      code:[null],
      quantity:[null, Validators.required],
      budget:[null],
      payed:[false, Validators.required],
      is_gift:[false, Validators.required]
    });
  }

  ngOnInit() {
    this.id = this.activatedRoute.snapshot.params.id;

    if (!this.id || this.id === null) {
      this.mode = 'create';
    } else {
      this.mode = 'update';
      this.getVoucher();
    }

    this.getClients();
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
      remaining_balance: this.defaults.quantity,
      payed: this.defaults.payed,
      is_gift: this.defaults.is_gift,
      client_id: this.defaults.client_id.id,
      school_id: this.user.schools[0].id
    };

    this.crudService.create('/vouchers', data)
      .subscribe((res) => {
        this.snackbar.open(this.translateService.instant('snackbar.bonus.create'), 'OK', {duration: 3000});
        this.router.navigate(['/vouchers'])
      })
  }

  update() {

    const data = {
      code: this.defaults.code,
      quantity: this.defaults.quantity,
      remaining_balance: this.defaults.payed ? 0 : this.defaults.quantity,
      payed: this.defaults.payed,
      is_gift: this.defaults.payed,
      client_id: this.defaults.client_id.id,
      school_id: this.user.schools[0].id
    };

    this.crudService.update('/vouchers', data, this.id)
      .subscribe((res) => {

        this.snackbar.open(this.translateService.instant('snackbar.bonus.update'), 'OK', {duration: 3000});
        this.router.navigate(['/vouchers'])
      })

  }

  generateRandomCode() {
    this.defaults.code = "BOU-"+this.generateRandomNumber();
  }
  // pasar a utils
  private _filter(name: string): any[] {
    const filterValue = name.toLowerCase();
    return this.clients.filter(client => (client.first_name.toLowerCase().includes(filterValue) || client.last_name.toLowerCase().includes(filterValue)));
  }


  displayFn(client: any): string {
    return client && client?.first_name && client?.last_name ? client?.first_name + ' ' + client?.last_name : client?.first_name;
  }

  getClients() {
    this.crudService.list('/clients', 1, 10000, 'desc', 'id', '&school_id='+this.user.schools[0].id)
      .subscribe((data: any) => {
        this.clients = data.data;
        this.filteredOptions = this.clientsForm.valueChanges.pipe(
          startWith(''),
          map((value: any) => typeof value === 'string' ? value : value?.name),
          map(full_name => full_name ? this._filter(full_name) : this.clients.slice(0, 50))
        );

        if (this.mode === 'update') {
          this.defaults.client_id = this.clients.find((c) => this.defaults.client_id === c.id);
        }
        this.loading = false;
      })
  }

  getVoucher() {
    this.crudService.get('/vouchers/'+this.id)
      .subscribe((data: any) => {
        this.defaults = data.data;

        this.getVoucherLogs();
      })
  }

  getVoucherLogs() {
    this.crudService.list('/vouchers-logs', 1, 10000, 'desc', 'id', '&voucher_id='+this.id)
    .subscribe((vl) => {
        this.logs = vl.data;
        this.loading = false;
      }
    )
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
