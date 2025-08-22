import { Component } from '@angular/core';
import { ComponentFixture, TestBed } from '@angular/core/testing';
import { AuthShellComponent } from './auth-shell.component';

describe('AuthShellComponent', () => {
  let component: AuthShellComponent;
  let fixture: ComponentFixture<AuthShellComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [AuthShellComponent],
    }).compileComponents();

    fixture = TestBed.createComponent(AuthShellComponent);
    component = fixture.componentInstance;
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });

  it('should render inputs and features', () => {
    component.brandLine = 'Brand';
    component.title = 'Title';
    component.subtitle = 'Subtitle';
    component.features = [
      { icon: 'icon-a', title: 'Feature A', subtitle: 'Feature a' },
    ];
    fixture.detectChanges();
    const compiled = fixture.nativeElement as HTMLElement;
    expect(compiled.querySelector('.brand-line')?.textContent).toBe('Brand');
    expect(compiled.querySelector('#auth-title')?.textContent).toBe('Title');
    expect(compiled.querySelector('.subtitle')?.textContent).toBe('Subtitle');
    expect(compiled.querySelectorAll('.features li').length).toBe(1);
  });

  it('should project auth-form content', () => {
    @Component({
      selector: 'test-host',
      template: `
        <bk-auth-shell>
          <form auth-form><span class="inner">Form</span></form>
        </bk-auth-shell>
      `,
      standalone: true,
      imports: [AuthShellComponent],
    })
    class TestHostComponent {}

    const hostFixture = TestBed.createComponent(TestHostComponent);
    hostFixture.detectChanges();
    const inner = hostFixture.nativeElement.querySelector('.inner');
    expect(inner?.textContent).toBe('Form');
  });
});
