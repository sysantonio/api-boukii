import { Component, Inject, OnInit } from '@angular/core';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';
import { ApiCrudService } from 'src/service/crud.service';
import {MatSnackBar} from '@angular/material/snack-bar';
import {TranslateService} from '@ngx-translate/core';

@Component({
  selector: 'vex-add-discount-bonus',
  templateUrl: './add-discount-bonus.component.html',
  styleUrls: ['./add-discount-bonus.component.scss']
})
export class AddDiscountBonusModalComponent implements OnInit {

  bonuses: any;
  bonus: any;
  voucherName: string = '';
  constructor(@Inject(MAT_DIALOG_DATA) public defaults: any, private crudService: ApiCrudService,
              private dialogRef: MatDialogRef<any>, private snackbar: MatSnackBar,
              private translateService: TranslateService) {

  }

  ngOnInit(): void {

    this.crudService.list('/vouchers', 1, 10000, 'desc', 'id', '&school_id=' + this.defaults.school_id + '&client_id=' + this.defaults.client_id + '&payed=0')
      .subscribe((data) => {
        this.bonuses = data.data;
      })
  }

  searchVoucher() {
    this.crudService.list('/vouchers', 1, 10000, 'desc', 'id',
      '&code='+this.voucherName+'&school_id=' + this.defaults.school_id  + '&payed=0')
      .subscribe((data) => {
        if(data.data.length) {
          this.bonuses = [...data.data];
        } else {
          this.snackbar.open(this.translateService.instant('No_vouchers_found'), 'OK', {duration: 3000});
        }

      })
  }

  closeModal() {

    this.bonus.reducePrice = this.bonus.remaining_balance > this.defaults.currentPrice ? this.defaults.currentPrice : this.bonus.remaining_balance;

    this.dialogRef.close({
      bonus: this.bonus
    })
  }

  isInUse(id: number) {
    let inUse = false;
    this.defaults.appliedBonus.forEach(element => {
      if (element.bonus.id === id) {
        inUse = true;
      }
    });

    return inUse;
  }
}
