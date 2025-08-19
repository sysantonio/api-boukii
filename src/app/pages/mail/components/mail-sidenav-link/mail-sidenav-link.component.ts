import { Component, Input } from '@angular/core';
import { MailSidenavLink } from '../../interfaces/mail-sidenav-link.interface';

@Component({
  selector: 'vex-mail-sidenav-link',
  templateUrl: './mail-sidenav-link.component.html',
  styleUrls: ['./mail-sidenav-link.component.scss']
})
export class MailSidenavLinkComponent {

  @Input() link: MailSidenavLink;

}
