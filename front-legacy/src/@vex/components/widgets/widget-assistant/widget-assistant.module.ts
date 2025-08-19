import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { WidgetAssistantComponent } from './widget-assistant.component';
import { MatIconModule } from '@angular/material/icon';
import { TranslateModule } from '@ngx-translate/core';
import {MatDatepickerModule} from '@angular/material/datepicker';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';


@NgModule({
  declarations: [WidgetAssistantComponent],
  imports: [
    CommonModule,
    TranslateModule,
    MatIconModule,
    FormsModule,
    CommonModule,
    ReactiveFormsModule,
    MatFormFieldModule,
    MatDatepickerModule,
    MatInputModule
  ],
  exports: [WidgetAssistantComponent]
})
export class WidgetAssistantModule {
}
