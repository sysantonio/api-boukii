import { Component, Input } from '@angular/core';
import { CommonModule } from '@angular/common';
import { TranslatePipe } from '@shared/pipes/translate.pipe';

@Component({
  selector: 'auth-layout',
  standalone: true,
  imports: [CommonModule, TranslatePipe],
  templateUrl: './auth-layout.component.html',
  styleUrls: ['./auth-layout.component.scss']
})
export class AuthLayoutComponent {
  @Input() title = '';
  @Input() subtitle = '';
}