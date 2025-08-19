import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { DiscountsComponent } from './discounts.component';
import { BreadcrumbsModule } from '../../../@vex/components/breadcrumbs/breadcrumbs.module';
import { MatIconModule } from '@angular/material/icon';
import { SecondaryToolbarModule } from '../../../@vex/components/secondary-toolbar/secondary-toolbar.module';
import { ComponentsModule } from '../../../@vex/components/components.module';
import { PageLayoutModule } from '../../../@vex/components/page-layout/page-layout.module';
import { LayoutModule } from '../../../@vex/layout/layout.module';
import { RouterModule } from '@angular/router';
import { ChartModule } from '../../../@vex/components/chart/chart.module';
import {
  WidgetQuickLineChartModule
} from '../../../@vex/components/widgets/widget-quick-line-chart/widget-quick-line-chart.module';
import {
  WidgetQuickValueCenterModule
} from '../../../@vex/components/widgets/widget-quick-value-center/widget-quick-value-center.module';
import {
  WidgetQuickValueStartModule
} from '../../../@vex/components/widgets/widget-quick-value-start/widget-quick-value-start.module';
import {
  WidgetLargeGoalChartModule
} from '../../../@vex/components/widgets/widget-large-goal-chart/widget-large-goal-chart.module';
import { WidgetAssistantModule } from '../../../@vex/components/widgets/widget-assistant/widget-assistant.module';
import { WidgetLargeChartModule } from '../../../@vex/components/widgets/widget-large-chart/widget-large-chart.module';
import { WidgetTableModule } from '../../../@vex/components/widgets/widget-table/widget-table.module';
import { MatDialogModule } from '@angular/material/dialog';
import { DiscountsRoutingModule } from './discounts-routing.module';
import { DiscountsCreateUpdateModule } from './discounts-create-update/discounts-create-update.module';


@NgModule({
  declarations: [
    DiscountsComponent
  ],
  imports: [
    CommonModule,
    LayoutModule,
    PageLayoutModule,
    BreadcrumbsModule,
    RouterModule,
    DiscountsRoutingModule,
    SecondaryToolbarModule,
    MatIconModule,
    ChartModule,
    WidgetQuickLineChartModule,
    WidgetQuickValueCenterModule,
    WidgetQuickValueStartModule,
    WidgetLargeGoalChartModule,
    WidgetAssistantModule,
    WidgetLargeChartModule,
    WidgetTableModule,
    //LanguageCreateUpdateModule,
    MatDialogModule,
    ComponentsModule,
    DiscountsCreateUpdateModule
  ]
})
export class DiscountsModule { }
