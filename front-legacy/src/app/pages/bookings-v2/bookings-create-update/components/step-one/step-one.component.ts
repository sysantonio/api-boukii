import { Component, Input, OnInit, Output, EventEmitter } from "@angular/core";
import { FormBuilder, FormGroup, Validators } from "@angular/forms";
import { Observable, debounceTime, map, skip, startWith } from "rxjs";
import { ApiCrudService } from "src/service/crud.service";
import { ApiResponse } from "src/app/interface/api-response";
import { MatDialog } from '@angular/material/dialog';
import { UtilsService } from '../../../../../../service/utils.service';
import { switchMap } from 'rxjs/operators';
import { ClientCreateUpdateModalComponent } from "src/app/pages/clients/client-create-update-modal/client-create-update-modal.component";

@Component({
  selector: "booking-step-one",
  templateUrl: "./step-one.component.html",
  styleUrls: ["./step-one.component.scss"],
})
export class StepOneComponent implements OnInit {
  @Input() initialData: any;
  @Input() allLevels: any;
  @Output() stepCompleted = new EventEmitter<FormGroup>();

  stepOneForm: FormGroup;
  user: any;
  filteredOptions: Observable<any[]>;
  selectedClient: any;
  mainClient: any;
  expandClients: any[];
  userAvatar = "../../../../assets/img/avatar.png";

  constructor(private fb: FormBuilder, private crudService: ApiCrudService, protected utilsService: UtilsService,
    private dialog: MatDialog) { }

  ngOnInit(): void {
    this.user = JSON.parse(localStorage.getItem("boukiiUser"));
    this.selectedClient = this.initialData?.client;
    this.mainClient = this.initialData?.mainClient;
    this.getClients().subscribe({ next: (response) => this.expandClients = this.getExpandClients(response.data) });
    this.stepOneForm = this.fb.group({
      client: [this.selectedClient || "", Validators.required],
      mainClient: [this.mainClient, Validators.required],
    });
    this.filteredOptions = this.stepOneForm.get('client')!.valueChanges.pipe(
      startWith(''),
      debounceTime(300),
      switchMap((value: string) =>
        this.crudService.list(
          '/admin/clients/mains',
          1,
          50, // Solo 50 resultados por página
          'desc',
          'id',
          `&school_id=${this.user.schools[0].id}&active=1&search=${value}`
        )
      ),
      map((response: any) => this.getExpandClients(response.data)),
    );
  }

  setClient(ev: any) {
    this.selectedClient = ev;
    this.stepOneForm.patchValue({
      client: ev,
      mainClient: this.selectedClient.main_client || this.selectedClient,
    });
  }

  addClient() {
    const dialogRef = this.dialog.open(ClientCreateUpdateModalComponent, {
      width: '1000px',
      height: 'max-content',
      panelClass: 'full-screen-dialog',
      data: { id: this.user.schools[0].id }
    });

    dialogRef.afterClosed().subscribe((data: any) => {
      if (data) {
        this.getClients().subscribe({
          next: (response) => {
            this.filteredOptions = this.stepOneForm.get('client')!.valueChanges.pipe(
              startWith(''),
              debounceTime(300),
              switchMap((value: string) =>
                this.crudService.list(
                  '/admin/clients/mains',
                  1,
                  50,
                  'desc',
                  'id',
                  `&school_id=${this.user.schools[0].id}&active=1&search=${value}`
                )
              ),
              map((response: any) => this.getExpandClients(response.data)),
            );
            this.expandClients = this.getExpandClients(response.data)
            const newClient = this.expandClients.find((c) => c.id = data.data.data.id);
            this.setClient(newClient)
          }
        });
      }
    })
  }

  displayFn(client: any): string {
    return client && client?.first_name && client?.last_name
      ? client?.first_name + " " + client?.last_name
      : client?.first_name;
  }

  isFormValid() {
    return (
      this.stepOneForm.valid &&
      this.selectedClient === this.stepOneForm.get("client").value
    );
  }

  getClients(): Observable<ApiResponse> {
    return this.crudService.list(
      "/admin/clients/mains",
      1,
      50,
      "desc",
      "id",
      "&school_id=" + this.user.schools[0].id + "&active=1"
    );
  }

  completeStep() {
    if (this.isFormValid()) {
      this.stepCompleted.emit(this.stepOneForm);
    }
  }

  private getExpandClients(clients: any[]): any[] {
    return clients.reduce((expanded, client) => {
      expanded.push(client);
      if (client.utilizers?.length) {
        const utilizers = client.utilizers.map(utilizer => ({
          ...utilizer,
          main_client: client,
        }));
        expanded.push(...utilizers);
      }
      return expanded;
    }, []);
  }

  private _filter(name: string): any[] {
    const filterValue = name.toLowerCase();
    return this.expandClients.filter(
      (client) =>
        client.first_name.toLowerCase().includes(filterValue) ||
        client.last_name.toLowerCase().includes(filterValue)
    );
  }
}
