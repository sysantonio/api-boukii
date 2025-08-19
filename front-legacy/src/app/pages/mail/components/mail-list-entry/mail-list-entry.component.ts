import { ChangeDetectorRef, Component, EventEmitter, Input, OnInit, Output } from '@angular/core';
import { MatCheckboxChange } from '@angular/material/checkbox';
import { MailService } from '../../services/mail.service';
import { MailComposeComponent } from '../mail-compose/mail-compose.component';
import { MatDialog } from '@angular/material/dialog';

@Component({
  selector: 'vex-mail-list-entry',
  templateUrl: './mail-list-entry.component.html',
  styleUrls: ['./mail-list-entry.component.scss']
})
export class MailListEntryComponent implements OnInit {

  @Input() mail: any;
  @Input() selected: boolean;
  @Output() selectedChange = new EventEmitter<boolean>();

  hovered: boolean;

  constructor(private cd: ChangeDetectorRef,
              private mailService: MailService,
              private dialog: MatDialog) { }

  ngOnInit(): void {
  }

  isCheckboxVisible() {
    return this.selected || this.hovered;
  }

  isStarVisible() {
    return this.mail?.starred || this.isCheckboxVisible();
  }

  onMouseEnter() {
    this.hovered = true;
    this.cd.markForCheck();
  }

  onMouseLeave() {
    this.hovered = false;
    this.cd.markForCheck();
  }

  onCheckboxChange(event: MatCheckboxChange) {
    this.selectedChange.emit(event.checked);
  }

  toggleStar(event: MouseEvent) {
    event?.preventDefault();
    event?.stopPropagation();

    this.mail.starred = !this.mail.starred;
    this.cd.markForCheck();
  }

  markMailAsRead(mailId: any['id']) {
    this.mailService.markMailAsRead(mailId);
  }

  openCompose() {
    const dialog = this.dialog.open(MailComposeComponent, {
      width: '100%',
      maxWidth: 1800,

    });

    dialog.afterClosed().subscribe((data) => {
      this.mailService.getData();
    })
  }
}
