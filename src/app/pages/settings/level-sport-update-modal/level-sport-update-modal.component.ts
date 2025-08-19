import { Component, Inject, OnInit, ViewChild } from '@angular/core';
import { UntypedFormBuilder, UntypedFormGroup, Validators } from '@angular/forms';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';
import { MatTable, MatTableDataSource } from '@angular/material/table';
@Component({
  selector: 'vex-level-sport-update-modal',
  templateUrl: './level-sport-update-modal.component.html',
  styleUrls: ['./level-sport-update-modal.component.scss']
})
export class LevelSportUpdateModalComponent {

  @ViewChild('objectiveTable') table: MatTable<any>;

  loading: boolean = true;
  form: UntypedFormGroup;
  mode: 'create' | 'update' = 'create';

  selectedObjective = new MatTableDataSource([]);
  displayedObjectiveColumns: string[] = ['name'];

  constructor(@Inject(MAT_DIALOG_DATA) public defaults: any, private dialogRef: MatDialogRef<any>, private fb: UntypedFormBuilder) {

  }

  ngOnInit(): void {

    this.selectedObjective.data = this.defaults.goals;
    this.form = this.fb.group({
      name: ['', Validators.required],
      payment: ['', Validators.required]
    })

    this.loading = false;
  }

  save() {
    if (this.mode === 'create') {
      this.create();
    } else if (this.mode === 'update') {
      this.update();
    }
  }

  create() {
    const booking = this.form.value;

    if (!booking.imageSrc) {
      booking.imageSrc = 'assets/img/avatars/1.jpg';
    }

    this.dialogRef.close(booking);
  }

  update() {
    const booking = this.form.value;
    booking.id = this.defaults.id;

    this.dialogRef.close(booking);
  }

  isCreateMode() {
    return this.mode === 'create';
  }

  isUpdateMode() {
    return this.mode === 'update';
  }

  removeObjective(index: number) {
    const data = this.selectedObjective.data;
    data.splice(index, 1);
    this.selectedObjective.data = data;
    this.table.renderRows();
  }

  onAddObjective(): void {
    // Código para manejar la acción de añadir un objetivo
    const newObjective: any = {
      id: null, // Aquí deberías generar un nuevo ID o tener una lógica para asignarlo
      name: "",
      sport: {
        id: 1,
        name: "Ski",
        icon: "https://api.boukii.com/storage/apps/sports/Ski.png",
        sport_type: 1
      },
      degree: {
        id: 1,
        league: "SKV",
        level: "Ptit Loup",
        name: "Ptit Loup",
        degree_order: 1,
        progress: 33,
        color: "#1C482C"
      }
    };

    // Agrega el nuevo objetivo al DataSource
    this.selectedObjective.data = [...this.selectedObjective.data, newObjective];
  }
}
