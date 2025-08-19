import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatIconModule } from '@angular/material/icon';
import { MatTooltipModule } from '@angular/material/tooltip';
import { MatButtonModule } from '@angular/material/button';
import { ShareBottomSheetModule } from '../../share-bottom-sheet/share-bottom-sheet.module';
import { WidgetQuickValueCenterCustomComponent } from './widget-quick-value-center-custom.component';


@NgModule({
  declarations: [WidgetQuickValueCenterCustomComponent],
  imports: [
    CommonModule,
    MatIconModule,

    MatTooltipModule,
    MatButtonModule,
    ShareBottomSheetModule
  ],
  exports: [WidgetQuickValueCenterCustomComponent]
})
export class WidgetQuickValueCenterCustomModule {
}

