import { ComponentFixture, TestBed } from '@angular/core/testing';

import { BonusesCreateUpdateComponent } from './bonuses-create-update.component';

describe('BonusesCreateUpdateComponent', () => {
  let component: BonusesCreateUpdateComponent;
  let fixture: ComponentFixture<BonusesCreateUpdateComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ BonusesCreateUpdateComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(BonusesCreateUpdateComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
