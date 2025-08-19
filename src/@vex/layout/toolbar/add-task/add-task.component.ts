import { Component, Inject, OnInit } from '@angular/core';
import { FormControl, UntypedFormBuilder, UntypedFormGroup, Validators } from '@angular/forms';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';
import { MatSnackBar } from '@angular/material/snack-bar';
import { TranslateService } from '@ngx-translate/core';
import moment from 'moment';
import { Observable, map, startWith } from 'rxjs';
import { ApiCrudService } from 'src/service/crud.service';

@Component({
  selector: 'vex-add-task',
  templateUrl: './add-task.component.html',
  styleUrls: ['./add-task.component.scss']
})
export class AddTaskComponent implements OnInit {

  form: UntypedFormGroup;
  selectedHour: any;
  selectedDate: any;
  hours: any = [];
  today: Date;
  minDate: Date;
  user: any;

  constructor(@Inject(MAT_DIALOG_DATA) public defaults: any, private crudService: ApiCrudService, private fb: UntypedFormBuilder, private dialogRef: MatDialogRef<any>) {
    this.user = JSON.parse(localStorage.getItem('boukiiUser'));

    this.today = new Date();
    this.minDate = new Date(this.today);
  }

  ngOnInit(): void {
    this.form = this.fb.group({
      name: [null, Validators.required],
      date: [null, Validators.required],
      time: [null, Validators.required]
    });
    this.generateHours();
  }


  save() {
    const data = {
      school_id: this.user.schools[0].id,
      time: this.selectedHour,
      date: moment(this.selectedDate).format('YYYY-MM-DD'),
      name: this.form.value.name
    };

    this.crudService.create('/tasks', data)
      .subscribe((d) => {
        this.dialogRef.close(d);
      })
  }

  generateHours() {
    for (let i = 8; i <= 17; i++) {
      for (let j = 0; j < 60; j += 5) {
        const formattedHour = `${i.toString().padStart(2, '0')}:${j.toString().padStart(2, '0')}`;
        this.hours.push(formattedHour);
      }
    }
  }

}
