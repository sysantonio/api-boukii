import { ComponentFixture, TestBed, waitForAsync } from '@angular/core/testing';
import { WidgetSummaryChartsComponent } from './widget-summary-charts.component';

describe('WidgetSummaryChartsComponent', () => {
  let component: WidgetSummaryChartsComponent;
  let fixture: ComponentFixture<WidgetSummaryChartsComponent>;

  beforeEach(waitForAsync(() => {
    TestBed.configureTestingModule({
      declarations: [WidgetSummaryChartsComponent]
    })
      .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(WidgetSummaryChartsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
