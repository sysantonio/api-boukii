import { ComponentFixture, TestBed } from '@angular/core/testing';

import { SalaryCreateUpdateModalComponent } from './salary-create-update-modal.component';

describe('SalaryCreateUpdateModalComponent', () => {
  let component: SalaryCreateUpdateModalComponent;
  let fixture: ComponentFixture<SalaryCreateUpdateModalComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ SalaryCreateUpdateModalComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(SalaryCreateUpdateModalComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
