import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ClientCreateUpdateComponent } from './client-create-update.component';

describe('ClientCreateUpdateComponent', () => {
  let component: ClientCreateUpdateComponent;
  let fixture: ComponentFixture<ClientCreateUpdateComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ ClientCreateUpdateComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(ClientCreateUpdateComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
