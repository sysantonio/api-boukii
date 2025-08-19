import { Component, Inject, OnInit } from '@angular/core';
import { FormControl, UntypedFormBuilder, UntypedFormGroup, Validators } from '@angular/forms';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';
import { MatSnackBar } from '@angular/material/snack-bar';
import { Observable, map, startWith } from 'rxjs';
import { ApiCrudService } from 'src/service/crud.service';

@Component({
  selector: 'vex-add-discount-bonus',
  templateUrl: './add-discount-bonus.component.html',
  styleUrls: ['./add-discount-bonus.component.scss']
})
export class AddDiscountBonusModalComponent implements OnInit {

  bonuses: any;
  bonus: any;
  constructor(@Inject(MAT_DIALOG_DATA) public defaults: any, private crudService: ApiCrudService, private dialogRef: MatDialogRef<any>) {

  }

  ngOnInit(): void {

    this.crudService.list('/vouchers', 1, 10000, 'desc', 'id', '&school_id='+this.defaults.school_id + '&client_id='+this.defaults.client_id + '&payed=0')
      .subscribe((data) => {
        this.bonuses = data.data;
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
