import { ComponentFixture, TestBed, waitForAsync } from '@angular/core/testing';
import { WidgetClientsGroupComponent } from './widget-clients-group.component';

describe('WidgetClientsGroupComponent', () => {
  let component: WidgetClientsGroupComponent;
  let fixture: ComponentFixture<WidgetClientsGroupComponent>;

  beforeEach(waitForAsync(() => {
    TestBed.configureTestingModule({
      declarations: [WidgetClientsGroupComponent]
    })
      .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(WidgetClientsGroupComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
