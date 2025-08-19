import { ComponentFixture, TestBed } from '@angular/core/testing';

import { AdminCreateUpdateComponent } from './admin-create-update.component';

describe('AdminCreateUpdateComponent', () => {
  let component: AdminCreateUpdateComponent;
  let fixture: ComponentFixture<AdminCreateUpdateComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ AdminCreateUpdateComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(AdminCreateUpdateComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
