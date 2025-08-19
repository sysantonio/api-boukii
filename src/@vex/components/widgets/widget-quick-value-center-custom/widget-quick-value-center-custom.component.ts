import { ChangeDetectionStrategy, Component, Input, OnInit } from '@angular/core';
import { MatBottomSheet } from '@angular/material/bottom-sheet';
import { ShareBottomSheetComponent } from '../../share-bottom-sheet/share-bottom-sheet.component';
import { scaleInOutAnimation } from '../../../animations/scale-in-out.animation';

@Component({
  selector: 'vex-widget-quick-value-center-custom',
  templateUrl: './widget-quick-value-center-custom.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
  animations: [scaleInOutAnimation]
})
export class WidgetQuickValueCenterCustomComponent implements OnInit {

  @Input() icon: string;
  @Input() value: string;
  @Input() label: string;
  @Input() change: number;
  @Input() helpText: string;
  @Input() iconClass: string;
  @Input() canShare: boolean;
  @Input() withPercent: boolean = true;

  showButton: boolean;

  constructor(private _bottomSheet: MatBottomSheet) { }

  ngOnInit() {
  }

  openSheet() {
    this._bottomSheet.open(ShareBottomSheetComponent);
  }
}
