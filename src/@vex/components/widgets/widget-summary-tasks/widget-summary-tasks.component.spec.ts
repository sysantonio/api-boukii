import { ComponentFixture, TestBed, waitForAsync } from '@angular/core/testing';
import { WidgetSummaryTasksComponent } from './widget-summary-tasks.component';


describe('WidgetSummaryTasksComponent', () => {
  let component: WidgetSummaryTasksComponent;
  let fixture: ComponentFixture<WidgetSummaryTasksComponent>;

  beforeEach(waitForAsync(() => {
    TestBed.configureTestingModule({
      declarations: [WidgetSummaryTasksComponent]
    })
      .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(WidgetSummaryTasksComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
