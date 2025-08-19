import { ComponentFixture, TestBed } from '@angular/core/testing';

import { LevelSportUpdateModalComponent } from './level-sport-update-modal.component';

describe('LevelSportUpdateModalComponent', () => {
  let component: LevelSportUpdateModalComponent;
  let fixture: ComponentFixture<LevelSportUpdateModalComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ LevelSportUpdateModalComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(LevelSportUpdateModalComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
