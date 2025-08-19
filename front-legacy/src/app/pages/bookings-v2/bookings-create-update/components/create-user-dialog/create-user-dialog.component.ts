import { Component, Inject, OnInit } from "@angular/core";
import { FormBuilder, FormGroup, Validators } from "@angular/forms";
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';
import { Observable } from "rxjs";
import { ApiResponse } from "src/app/interface/api-response";
import { ApiCrudService } from "src/service/crud.service";
import { LangService } from "src/service/langService";

@Component({
  selector: "booking-create-user-dialog",
  templateUrl: "./create-user-dialog.component.html",
  styleUrls: ["./create-user-dialog.component.scss"],
})
export class CreateUserDialogComponent implements OnInit {
  constructor(
    private fb: FormBuilder,
    private crudService: ApiCrudService,
    private langService: LangService,
    @Inject(MAT_DIALOG_DATA) public data: any,
    private dialogRef: MatDialogRef<any>
  ) { }

  user: any;
  expandClients: any[];
  stepForm: FormGroup;
  languages: any;
  today: Date = new Date()
  ngOnInit(): void {
    this.user = JSON.parse(localStorage.getItem("boukiiUser"));
    this.languages = this.langService.getLanguages();
    this.getClients().subscribe({ next: (response) => this.expandClients = this.getExpandClients(response.data) });
    this.stepForm = this.fb.group({
      name: ["", Validators.required],
      surname: ["", Validators.required],
      birthDate: ["", Validators.required],
      lenguages: ["", Validators.required],
    });

  }
  getClients(): Observable<ApiResponse> {
    return this.crudService.list(
      "/admin/clients/mains",
      1,
      10000,
      "desc",
      "id",
      "&school_id=" + this.user.schools[0].id + "&active=1"
    );
  }

  isFormValid() {
    return this.stepForm.valid;
  }

  handleConfirm() {
    const data = this.stepForm.value;
    this.dialogRef.close({
      action: 'new',
      data: data,
    })
  }

  private getExpandClients(clients: any[]): any[] {
    let expandedClients = [];
    clients.forEach((client) => {
      expandedClients.push(client);
      client.utilizers?.forEach((utilizer) => {
        let expandedUtilizer = { ...utilizer, main_client: client };
        expandedClients.push(expandedUtilizer);
      });
    });
    return expandedClients;
  }

}
