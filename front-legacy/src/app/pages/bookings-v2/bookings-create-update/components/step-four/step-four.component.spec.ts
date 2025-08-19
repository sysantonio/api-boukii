import { ComponentFixture, TestBed } from '@angular/core/testing';

import { StepFourComponent } from './step-four.component';

describe('StepFourComponent', () => {
  let component: StepFourComponent;
  let fixture: ComponentFixture<StepFourComponent>;

  beforeEach(() => {
    TestBed.configureTestingModule({
      declarations: [StepFourComponent]
    });
    fixture = TestBed.createComponent(StepFourComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
