import { Component, Input } from '@angular/core';
import { MailSidenavLink } from '../../interfaces/mail-sidenav-link.interface';
import { MatDrawer } from '@angular/material/sidenav';
import { stagger40ms } from 'src/@vex/animations/stagger.animation';
import { fadeInUp400ms } from 'src/@vex/animations/fade-in-up.animation';
import { LayoutService } from 'src/@vex/services/layout.service';
import { MailComponent } from '../../containers/mail.component';

@Component({
  selector: 'vex-mail-sidenav',
  templateUrl: './mail-sidenav.component.html',
  styleUrls: ['./mail-sidenav.component.scss'],
  animations: [
    stagger40ms,
    fadeInUp400ms
  ]
})
export class MailSidenavComponent {

  @Input() drawer: MatDrawer;

  constructor(private layoutService: LayoutService, public MailComponent: MailComponent) { }

  compose: MailSidenavLink = {
    label: 'compose',
    route: [],
    icon: 'mat:all_inbox'
  }
  links: MailSidenavLink[] = [
    /*{
      label: 'Inbox',
      route: ['./inbox'],
      icon: 'mat:inbox'
    },*/
    {
      label: 'general_mails',
      route: ['./general'],
      icon: 'mat:all_inbox'
    },
    /*    {
          label: 'automatic_mails',
          route: ['./auto'],
          icon: 'mat:all_inbox'
        },*/
    /*{
      label: 'Starred',
      route: ['./starred'],
      icon: 'mat:star'
    },
    {
      label: 'Drafts',
      route: ['./drafts'],
      icon: 'mat:drafts'
    },
    {
      label: 'Sent',
      route: ['./sent'],
      icon: 'mat:send'
    }*/
  ];

  labelLinks: MailSidenavLink[] = [
    /*{
      label: 'Important',
      route: ['./important'],
      icon: 'mat:label_important'
    },
    {
      label: 'Business',
      route: ['./business'],
      icon: 'mat:business'
    },
    {
      label: 'Secret',
      route: ['./secret'],
      icon: 'mat:lock'
    }*/
  ];

  closeDrawer() {
    if (this.layoutService.isLtLg()) {
      this.drawer?.close();
    }
  }
}
