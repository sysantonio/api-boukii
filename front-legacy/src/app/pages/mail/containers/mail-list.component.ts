import { Component, OnInit } from '@angular/core';
import { MailService } from '../services/mail.service';
import { SelectionModel } from '@angular/cdk/collections';
import { MatCheckboxChange } from '@angular/material/checkbox';
import { NavigationEnd, Router } from '@angular/router';
import { distinctUntilChanged, filter, map } from 'rxjs/operators';
import { fadeInUp400ms } from 'src/@vex/animations/fade-in-up.animation';
import { stagger40ms } from 'src/@vex/animations/stagger.animation';
import { getAllParams } from 'src/@vex/utils/check-router-childs-data';
import { trackById } from 'src/@vex/utils/track-by';
import { LayoutService } from 'src/@vex/services/layout.service';

@Component({
  selector: 'vex-mail-list',
  templateUrl: './mail-list.component.html',
  styleUrls: ['./mail-list.component.scss'],
  animations: [
    fadeInUp400ms,
    stagger40ms
  ]
})
export class MailListComponent implements OnInit {

  mails$ = this.mailService.filteredMails$;
  gtSm$ = this.layoutService.gtSm$;
  mails = this.mailService.mails;

  hasActiveMail$ = this.router.events.pipe(
    filter(event => event instanceof NavigationEnd),
    map(() => getAllParams(this.router.routerState.root.snapshot)),
    map(params => params.has('mailId')),
    distinctUntilChanged()
  );

  trackById = trackById;

  selection = new SelectionModel<any['id']>(true, []);

  constructor(private mailService: MailService,
              private layoutService: LayoutService,
              private router: Router) { }

  ngOnInit(): void {
  }

  masterToggle(mails: any[] | null, change: MatCheckboxChange) {
    if (change.checked) {
      this.selection.select(...mails?.map(mail => mail.id));
    } else {
      this.selection.deselect(...mails?.map(mail => mail.id));
    }
  }

  isAllSelected(mails: any[]): boolean {
    return mails?.length > 0 && mails?.length === this.selection.selected?.length;
  }

  isSomeButNotAllSelected(mails: any[]): boolean {
    return !this.isAllSelected(mails) && this.selection.hasValue();
  }

  checkMailType(mail: any) {
    return (location.pathname.includes('auto') && mail.lang) || (location.pathname.includes('general') && !mail.lang);
  }
}
