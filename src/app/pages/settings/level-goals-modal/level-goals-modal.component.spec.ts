import { ComponentFixture, TestBed } from '@angular/core/testing';

import { LevelGoalsModalComponent } from './level-goals-modal.component';

describe('LevelGoalsModalComponent', () => {
  let component: LevelGoalsModalComponent;
  let fixture: ComponentFixture<LevelGoalsModalComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ LevelGoalsModalComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(LevelGoalsModalComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
