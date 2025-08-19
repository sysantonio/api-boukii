import { ComponentFixture, TestBed } from '@angular/core/testing';
import { ReductionDialogComponent } from './reduction-dialog.component';


describe('ReductionDialogComponent', () => {
  let component: ReductionDialogComponent;
  let fixture: ComponentFixture<ReductionDialogComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ ReductionDialogComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(ReductionDialogComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
