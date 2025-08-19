import { Component, Input, OnInit } from '@angular/core';
import { trackByValue } from '../../utils/track-by';

@Component({
  selector: 'vex-breadcrumbs',
  template: `
    <ng-container *ngFor="let crumb of crumbs; trackBy: trackByValue">
      <ng-container *ngIf="crumb.link; else noLink">
        <a [routerLink]="crumb.link" class="breadcrumb-link">
          <ng-container *ngIf="crumb.title">
            <h2 class="title">{{crumb.text | translate}}</h2>
          </ng-container>
          <ng-container *ngIf="crumb.subtitle && crumb.text">
            <h3 class="subtitle">{{crumb.text | translate}}</h3>
          </ng-container>
          <ng-container *ngIf="crumb.icon">
            <i class="icon">
              <img src="../assets/img/icons/{{crumb.icon}}.svg" />
            </i>
          </ng-container>
        </a>
      </ng-container>
      <ng-template #noLink>
      <a class="breadcrumb-link">
        <ng-container *ngIf="crumb.title">
          <h2 class="title">{{crumb.text | translate}}</h2>
        </ng-container>
        <ng-container *ngIf="crumb.subtitle && crumb.text">
          <h3 class="subtitle">{{crumb.text | translate}}</h3>
        </ng-container>
        <ng-container *ngIf="crumb.icon">
          <i class="icon">
            <img src="../assets/img/icons/{{crumb.icon}}.svg" />
          </i>
        </ng-container>
        <ng-container *ngIf="crumb.icon2">
          <i class="icon">
            <img src="../assets/img/icons/{{crumb.icon2}}.png" />
          </i>
        </ng-container>
      </a>
      </ng-template>
    </ng-container>
  `
})
export class BreadcrumbsComponent {

  @Input() crumbs: any[] = [];

  trackByValue = trackByValue;

}
