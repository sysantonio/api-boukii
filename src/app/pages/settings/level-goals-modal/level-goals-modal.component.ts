import { Component, Inject, OnInit } from '@angular/core';
import { UntypedFormBuilder, UntypedFormGroup, Validators } from '@angular/forms';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';
import { MatSnackBar } from '@angular/material/snack-bar';
import { ApiCrudService } from 'src/service/crud.service';

@Component({
  selector: 'vex-level-goals-modal',
  templateUrl: './level-goals-modal.component.html',
  styleUrls: ['./level-goals-modal.component.scss']
})
export class LevelGoalsModalComponent implements OnInit {

  loading: boolean = true;
  form: UntypedFormGroup;
  mode: 'create' | 'update' = 'create';
  imagePreviewUrl: string | ArrayBuffer = null;
  goals: any = [];
  deleteGoals: any = [];

  constructor(@Inject(MAT_DIALOG_DATA) public defaults: any, private dialogRef: MatDialogRef<any>, private fb: UntypedFormBuilder,
    private crudService: ApiCrudService, private snackbar: MatSnackBar) {

  }

  ngOnInit(): void {

    if(this.defaults !== null && this.defaults.id) {
      this.mode = 'update';
      this.crudService.list('/degrees-school-sport-goals/', 1, 10000, 'desc', 'id', '&degree_id='+this.defaults.id)
        .subscribe((data: any)=> {
          if (data.data.length === 0) {
            this.mode = 'create';
          }

          this.imagePreviewUrl = this.defaults.image;

          this.goals = data.data;
          this.loading = false;

        })
    }

  }

  save() {
    this.crudService.update('/degrees', {image: this.imagePreviewUrl, active: this.defaults.active, color: this.defaults.color, degree_order: this.defaults.degree_order, league: this.defaults.league,
      level: this.defaults.level, name: this.defaults.name, progress: this.defaults.progress, sport_id: this.defaults.sport_id}, this.defaults.id)
      .subscribe(() => {

        this.goals.forEach(element => {
          element.image = this.imagePreviewUrl;
        });
        this.dialogRef.close({goals: this.goals, mode: this.mode, deletedGoals: this.deleteGoals});
      })
  }

  isCreateMode() {
    return this.mode === 'create';
  }

  isUpdateMode() {
    return this.mode === 'update';
  }

  setName(index: number, event: any) {
    this.goals[index].name = event.target.value;
  }

  onFileChanged(event: Event) {
    const file = (event.target as HTMLInputElement).files[0];
    if (file) {
      const reader = new FileReader();

      reader.onload = () => {
        this.imagePreviewUrl = reader.result;
      };

      reader.readAsDataURL(file);
    }
  }

  addGoal() {
    this.goals.push({
      degree_id: this.defaults.id,
      name: ''
    })
  }

  deleteGoal(idx: number) {
    if (this.mode !== 'create') {

      this.deleteGoals.push(this.goals[idx]);
    }
    this.goals.splice(idx, 1);
  }
}
