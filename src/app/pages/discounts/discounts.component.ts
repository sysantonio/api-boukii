import { Component } from '@angular/core';
import {TableColumn} from '../../../@vex/interfaces/table-column.interface';
import {DiscountsCreateUpdateComponent} from './discounts-create-update/discounts-create-update.component';

@Component({
  selector: 'vex-discounts',
  templateUrl: './discounts.component.html',
  styleUrls: ['./discounts.component.scss']
})
export class DiscountsComponent {

  createComponent = DiscountsCreateUpdateComponent;
  entity = '/discount-codes';
  deleteEntity = '/discount-codes';
  icon = '../../../assets/img/icons/bonos.svg';

  columns: TableColumn<any>[] = [
    { label: 'code', property: 'code', type: 'text', visible: true, cssClasses: ['font-medium'] },
    { label: 'quantity', property: 'quantity', type: 'text', visible: true },
    { label: 'bookings_page.reductions.percentaje', property: 'percentage', type: 'text', visible: true },
    { label: 'total', property: 'total', type: 'text', visible: true },
    { label: 'remaining_balance', property: 'remaining', type: 'text', visible: true },
    { label: 'register', property: 'created_at', type: 'date', visible: true },
    { label: 'Actions', property: 'actions', type: 'button', visible: true }
  ];

}
