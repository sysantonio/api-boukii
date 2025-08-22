import { Component, Input } from '@angular/core';
import { CommonModule } from '@angular/common';
import { A11yModule } from '@angular/cdk/a11y';

@Component({
  selector: 'bk-auth-shell',
  standalone: true,
  imports: [CommonModule, A11yModule],
  templateUrl: './auth-shell.component.html',
  styleUrls: ['./auth-shell.component.scss'],
})
export class AuthShellComponent {
  @Input() brandLine = '';
  @Input() title = '';
  @Input() subtitle = '';
  @Input() features: Array<{ icon: string; title: string; subtitle: string }> = [];
}
