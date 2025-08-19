import { Component, Inject, OnInit } from '@angular/core';
import { UntypedFormBuilder, UntypedFormGroup, Validators } from '@angular/forms';
import { MAT_DIALOG_DATA } from '@angular/material/dialog';

@Component({
  selector: 'vex-add-reduction',
  templateUrl: './add-reduction.component.html',
  styleUrls: ['./add-reduction.component.scss']
})
export class AddReductionModalComponent implements OnInit {

  type = 1;
  discount = 0;
  originalPrice = 0;
  totalDiscount = 0;
  form: UntypedFormGroup;
  constructor(@Inject(MAT_DIALOG_DATA) public defaults: any, private fb: UntypedFormBuilder) {
    this.originalPrice = defaults.currentPrice;
  }

  ngOnInit(): void {
    this.form = this.fb.group({
      discount: [0, Validators.required],
      totalDiscount: [0, Validators.required]
    });

    this.form.get('discount').valueChanges.subscribe((value) => {
      if (this.type === 1) { // Solo actualiza si estamos en porcentaje
        this.totalDiscount = (value / 100) * this.originalPrice;
      }
    });
  }

  onTabChange(index: number) {
    this.type = index === 0 ? 1 : 2;
  }

  updateFixedDiscount(event: any) {
    let value = event.target.value;
    if (this.type === 2) {
      this.totalDiscount = value;
    }
  }

}
