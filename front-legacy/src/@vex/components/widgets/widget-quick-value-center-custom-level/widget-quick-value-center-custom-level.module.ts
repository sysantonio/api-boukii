import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatIconModule } from '@angular/material/icon';
import { MatTooltipModule } from '@angular/material/tooltip';
import { MatButtonModule } from '@angular/material/button';
import { ShareBottomSheetModule } from '../../share-bottom-sheet/share-bottom-sheet.module';
import { WidgetQuickValueCenterCustomLevelComponent } from './widget-quick-value-center-custom-level.component';
import { MatSlideToggleModule } from '@angular/material/slide-toggle';


@NgModule({
  declarations: [WidgetQuickValueCenterCustomLevelComponent],
  imports: [
    CommonModule,
    MatIconModule,
    MatSlideToggleModule,
    MatTooltipModule,
    MatButtonModule,
    ShareBottomSheetModule
  ],
  exports: [WidgetQuickValueCenterCustomLevelComponent]
})
export class WidgetQuickValueCenterCustomLevelModule {
}

