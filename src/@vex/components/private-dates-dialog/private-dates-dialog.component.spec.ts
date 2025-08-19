import { ComponentFixture, TestBed } from '@angular/core/testing';
import { PrivateDatesDialogComponent } from './private-dates-dialog.component';


describe('PrivateDatesDialogComponent', () => {
  let component: PrivateDatesDialogComponent;
  let fixture: ComponentFixture<PrivateDatesDialogComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ PrivateDatesDialogComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(PrivateDatesDialogComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
