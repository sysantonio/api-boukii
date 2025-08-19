import { Component } from '@angular/core';
import { TableColumn } from 'src/@vex/interfaces/table-column.interface';
import { BonusesCreateUpdateComponent } from './bonuses-create-update/bonuses-create-update.component';

@Component({
  selector: 'vex-bonuses',
  templateUrl: './bonuses.component.html',
  styleUrls: ['./bonuses.component.scss']
})
export class BonusesComponent {

  createComponent = BonusesCreateUpdateComponent;
  entity = '/vouchers';
  deleteEntity = '/vouchers';
  icon = '../../../assets/img/icons/bonos.svg';

  columns: TableColumn<any>[] = [
    { label: 'code', property: 'code', type: 'text', visible: true, cssClasses: ['font-medium'] },
    { label: 'client', property: 'client', type: 'client', visible: true },
    { label: 'quantity', property: 'quantity', type: 'text', visible: true },
    { label: 'remaining_balance', property: 'remaining_balance', type: 'text', visible: true },
    { label: 'used', property: 'payed', type: 'status', visible: true },
    { label: 'register', property: 'created_at', type: 'date', visible: true },
    { label: 'Actions', property: 'actions', type: 'button', visible: true }
  ];
}
