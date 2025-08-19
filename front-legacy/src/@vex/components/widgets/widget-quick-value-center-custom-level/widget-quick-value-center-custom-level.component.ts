import { ChangeDetectionStrategy, Component, Input, OnInit } from '@angular/core';
import { MatBottomSheet } from '@angular/material/bottom-sheet';
import { ShareBottomSheetComponent } from '../../share-bottom-sheet/share-bottom-sheet.component';
import { scaleInOutAnimation } from '../../../animations/scale-in-out.animation';
import { MatDialog } from '@angular/material/dialog';
import { LevelSportUpdateModalComponent } from 'src/app/pages/settings/level-sport-update-modal/level-sport-update-modal.component';

@Component({
  selector: 'vex-widget-quick-value-center-custom-level',
  templateUrl: './widget-quick-value-center-custom-level.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
  animations: [scaleInOutAnimation]
})
export class WidgetQuickValueCenterCustomLevelComponent implements OnInit {

  @Input() icon: string;
  @Input() value: string;
  @Input() label: string;
  @Input() change: number;
  @Input() editText: string;
  @Input() canEdit: boolean = true;
  @Input() iconClass: string;
  @Input() level: any;
  @Input() canShare: boolean;
  @Input() withPercent: boolean = true;

  showButton: boolean;
  selectedLevels: number[] = [];

  constructor(private _bottomSheet: MatBottomSheet, private dialog: MatDialog) { }

  ngOnInit() {
  }

  openSheet() {
    this._bottomSheet.open(ShareBottomSheetComponent);
  }

  selectLevel(levelId: number): void {
    const index = this.selectedLevels.indexOf(levelId);
    if (index > -1) {
      this.selectedLevels.splice(index, 1);
    } else {
      this.selectedLevels.push(levelId);
    }
  }

  isLevelSelected(levelId: number): boolean {
    return this.selectedLevels.includes(levelId);
  }

  openModal(level: any) {

    const dialogRef = this.dialog.open(LevelSportUpdateModalComponent, {
      data: level,
      width: '90vw',
      height: '90vh',
      maxWidth: '100vw',  // Asegurarse de que no haya un ancho máximo
      panelClass: 'full-screen-dialog'  // Si necesitas estilos adicionales
    });

    dialogRef.afterClosed().subscribe((data: any) => {
      if (data) {

      }
    });
  }
}
