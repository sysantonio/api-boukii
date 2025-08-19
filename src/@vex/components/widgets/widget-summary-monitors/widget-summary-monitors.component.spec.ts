import { ComponentFixture, TestBed, waitForAsync } from '@angular/core/testing';
import { WidgetSummaryMonitorsComponent } from './widget-summary-monitors.component';


describe('WidgetSummaryMonitorsComponent', () => {
  let component: WidgetSummaryMonitorsComponent;
  let fixture: ComponentFixture<WidgetSummaryMonitorsComponent>;

  beforeEach(waitForAsync(() => {
    TestBed.configureTestingModule({
      declarations: [WidgetSummaryMonitorsComponent]
    })
      .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(WidgetSummaryMonitorsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
