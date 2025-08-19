import { Component, OnInit } from '@angular/core';
import { Notification } from '../interfaces/notification.interface';
import { DateTime } from 'luxon';
import { trackById } from '../../../../utils/track-by';

@Component({
  selector: 'vex-toolbar-notifications-dropdown',
  templateUrl: './toolbar-notifications-dropdown.component.html',
  styleUrls: ['./toolbar-notifications-dropdown.component.scss']
})
export class ToolbarNotificationsDropdownComponent implements OnInit {

  notifications: Notification[] = [
    {
      id: '1',
      label: 'default_noti',
      icon: 'mat:notifications',
      colorClass: 'text-primary',
      datetime: null
    }
  ];

  trackById = trackById;

  constructor() { }

  ngOnInit() {
  }

}
