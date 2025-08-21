import { Component, Input } from '@angular/core';
import { CommonModule } from '@angular/common';
import { TranslatePipe } from '@shared/pipes/translate.pipe';

interface AuthShellFeature {
  icon: string;
  titleKey: string;
  descKey: string;
}

@Component({
  selector: 'bk-auth-shell',
  standalone: true,
  imports: [CommonModule, TranslatePipe],
  templateUrl: './auth-shell.component.html',
  styleUrls: ['./auth-shell.component.scss'],
})
export class AuthShellComponent {
  @Input() titleKey = '';
  @Input() subtitleKey = '';
  @Input() features: AuthShellFeature[] = [];
}

