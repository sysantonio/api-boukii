import { Component } from '@angular/core';
import { AdminCreateUpdateComponent } from './admin-create-update/admin-create-update.component';
import { TableColumn } from 'src/@vex/interfaces/table-column.interface';

@Component({
  selector: 'vex-admins',
  templateUrl: './admins.component.html',
  styleUrls: ['./admins.component.scss']
})
export class AdminsComponent {

  createComponent = AdminCreateUpdateComponent;
  icon = '../../../assets/img/icons/Admins.svg';
  entity = '/users';
  deleteEntity = '/users';
  user: any;

  columns: TableColumn<any>[] = [
    { label: 'Id', property: 'id', type: 'text', visible: true, cssClasses: ['font-medium'] },
    { label: 'Email', property: 'email', type: 'text', visible: true, cssClasses: ['font-medium'] },
    { label: 'Actions', property: 'actions', type: 'button', visible: true }
  ];

  constructor() {
    this.user = JSON.parse(localStorage.getItem('boukiiUser'));
  }
}
