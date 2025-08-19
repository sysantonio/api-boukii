import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ExtraCreateUpdateModalComponent } from './extra-create-update-modal.component';

describe('ExtraCreateUpdateModalComponent', () => {
  let component: ExtraCreateUpdateModalComponent;
  let fixture: ComponentFixture<ExtraCreateUpdateModalComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ ExtraCreateUpdateModalComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(ExtraCreateUpdateModalComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
