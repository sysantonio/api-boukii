import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MailRoutingModule } from './mail-routing.module';
import { MailComponent } from './containers/mail.component';
import { MatSidenavModule } from '@angular/material/sidenav';
import { MatButtonModule } from '@angular/material/button';
import { MailListComponent } from './containers/mail-list.component';
import { MatCheckboxModule } from '@angular/material/checkbox';
import { MatIconModule } from '@angular/material/icon';
import { MailListEntryComponent } from './components/mail-list-entry/mail-list-entry.component';
import { MatRippleModule } from '@angular/material/core';
import { MailViewComponent } from './containers/mail-view.component';
import { MailViewEmptyComponent } from './containers/mail-view-empty.component';
import { MailSidenavComponent } from './components/mail-sidenav/mail-sidenav.component';
import { MailSidenavLinkComponent } from './components/mail-sidenav-link/mail-sidenav-link.component';
import { MatMenuModule } from '@angular/material/menu';
import { MailLabelComponent } from './components/mail-label/mail-label.component';
import { MailAttachmentComponent } from './components/mail-attachment/mail-attachment.component';
import { MailComposeComponent } from './components/mail-compose/mail-compose.component';
import { MatDialogModule } from '@angular/material/dialog';
import { MatInputModule } from '@angular/material/input';
import { MatTooltipModule } from '@angular/material/tooltip';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { ScrollbarModule } from 'src/@vex/components/scrollbar/scrollbar.module';
import { RelativeDateTimeModule } from 'src/@vex/pipes/relative-date-time/relative-date-time.module';
import { StripHtmlModule } from 'src/@vex/pipes/strip-html/strip-html.module';
import { MatRadioModule } from '@angular/material/radio';
import { MatTabsModule } from '@angular/material/tabs';
import { MatSelectModule } from '@angular/material/select';
import { TranslateModule } from '@ngx-translate/core';
import { MatDatepickerModule } from '@angular/material/datepicker';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { BreadcrumbsModule } from '../../../@vex/components/breadcrumbs/breadcrumbs.module';
import { SecondaryToolbarModule } from '../../../@vex/components/secondary-toolbar/secondary-toolbar.module';
import { AngularEditorModule } from '@kolkov/angular-editor';


@NgModule({
    declarations: [MailComponent, MailListComponent, MailListEntryComponent, MailViewComponent, MailViewEmptyComponent, MailSidenavComponent, MailSidenavLinkComponent, MailLabelComponent, MailAttachmentComponent, MailComposeComponent],
    imports: [
        CommonModule,
        FormsModule,
        MailRoutingModule,
        MatSidenavModule,
        ReactiveFormsModule,
        MatButtonModule,
        ScrollbarModule,
        MatCheckboxModule,
        MatIconModule,
        MatRippleModule,
        RelativeDateTimeModule,
        MatMenuModule,
        StripHtmlModule,
        MatDialogModule,
        MatInputModule, AngularEditorModule,
        MatRadioModule,
        MatTabsModule,
        MatSelectModule,
        TranslateModule,
        MatDatepickerModule,
        MatProgressSpinnerModule,
        MatTooltipModule,
        ReactiveFormsModule,
        BreadcrumbsModule,
        SecondaryToolbarModule,
    ]
})
export class MailModule {
}
