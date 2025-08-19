import { ComponentFixture, TestBed } from '@angular/core/testing';

import { BookingReservationDetailComponent } from './booking-reservation-detail.component';

describe('BookingReservationDetailComponent', () => {
  let component: BookingReservationDetailComponent;
  let fixture: ComponentFixture<BookingReservationDetailComponent>;

  beforeEach(() => {
    TestBed.configureTestingModule({
      declarations: [BookingReservationDetailComponent]
    });
    fixture = TestBed.createComponent(BookingReservationDetailComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
