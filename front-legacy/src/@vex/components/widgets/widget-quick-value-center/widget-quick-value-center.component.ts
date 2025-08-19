import { ChangeDetectionStrategy, Component, Input, OnInit } from '@angular/core';
import { MatBottomSheet } from '@angular/material/bottom-sheet';
import { ShareBottomSheetComponent } from '../../share-bottom-sheet/share-bottom-sheet.component';
import { scaleInOutAnimation } from '../../../animations/scale-in-out.animation';

@Component({
  selector: 'vex-widget-quick-value-center',
  templateUrl: './widget-quick-value-center.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
  animations: [scaleInOutAnimation]
})
export class WidgetQuickValueCenterComponent implements OnInit {

  @Input() icon: string;
  @Input() noIcon: boolean = false;
  @Input() value: string;
  @Input() bg: string = '';
  @Input() valueColor: string = '';
  @Input() label: string;
  @Input() label_secondary: string;
  @Input() change: any;
  @Input() helpText: string;
  @Input() iconClass: string;
  @Input() canShare: boolean;
  @Input() withPercent: boolean = false;
  @Input() onlyValue: boolean = true;

  showButton: boolean;

  constructor(private _bottomSheet: MatBottomSheet) { }

  ngOnInit() {
  }

  openSheet() {
    this._bottomSheet.open(ShareBottomSheetComponent);
  }
}
