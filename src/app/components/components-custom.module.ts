import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { LevelUserComponent } from './level-user/level-user.component';
import { ConfirmMailComponent } from './confirm-mail/confirm-mail.component';
import {TranslateModule} from '@ngx-translate/core';


@NgModule({
  declarations: [LevelUserComponent, ConfirmMailComponent],
    imports: [
        CommonModule, FormsModule, TranslateModule
    ],
    exports: [LevelUserComponent, ConfirmMailComponent]
})
export class ComponentsCustomModule { }
