import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ToolbarComponent } from './toolbar.component';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatMenuModule } from '@angular/material/menu';
import { MatRippleModule } from '@angular/material/core';
import { ToolbarNotificationsModule } from './toolbar-notifications/toolbar-notifications.module';
import { ToolbarUserModule } from './toolbar-user/toolbar-user.module';
import { ToolbarSearchModule } from './toolbar-search/toolbar-search.module';
import { NavigationModule } from '../navigation/navigation.module';
import { RouterModule } from '@angular/router';
import { NavigationItemModule } from '../../components/navigation-item/navigation-item.module';
import { MegaMenuModule } from '../../components/mega-menu/mega-menu.module';
import { TranslateModule } from '@ngx-translate/core';
import { AddTaskModule } from './add-task/add-task.module';
import { MatSlideToggleModule } from '@angular/material/slide-toggle';
import { FormsModule } from '@angular/forms';

@NgModule({
  declarations: [ToolbarComponent],
  imports: [
    CommonModule,
    MatButtonModule,
    MatIconModule,
    MatMenuModule,
    MatRippleModule,
    ToolbarNotificationsModule,
    ToolbarUserModule,
    ToolbarSearchModule,
    TranslateModule,
    NavigationModule,
    RouterModule,
    NavigationItemModule,
    MegaMenuModule,
    AddTaskModule,
    MatSlideToggleModule,
    FormsModule
  ],
  exports: [ToolbarComponent]
})
export class ToolbarModule {
}
