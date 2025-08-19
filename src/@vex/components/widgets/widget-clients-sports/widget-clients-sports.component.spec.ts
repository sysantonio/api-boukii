import { ComponentFixture, TestBed, waitForAsync } from '@angular/core/testing';
import { WidgetClientsSportsComponent } from './widget-clients-sports.component';

describe('WidgetClientsSportsComponent', () => {
  let component: WidgetClientsSportsComponent;
  let fixture: ComponentFixture<WidgetClientsSportsComponent>;

  beforeEach(waitForAsync(() => {
    TestBed.configureTestingModule({
      declarations: [WidgetClientsSportsComponent]
    })
      .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(WidgetClientsSportsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
