import { ComponentFixture, TestBed } from '@angular/core/testing';

import { DiscountsCreateUpdateComponent } from './discounts-create-update.component';

describe('DiscountsCreateUpdateComponent', () => {
  let component: DiscountsCreateUpdateComponent;
  let fixture: ComponentFixture<DiscountsCreateUpdateComponent>;

  beforeEach(() => {
    TestBed.configureTestingModule({
      declarations: [DiscountsCreateUpdateComponent]
    });
    fixture = TestBed.createComponent(DiscountsCreateUpdateComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
