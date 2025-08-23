import { Component, OnInit, inject, ChangeDetectionStrategy } from '@angular/core';
import { AppShellComponent } from '@ui/app-shell/app-shell.component';
import { AuthStore } from '@core/stores/auth.store';

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [AppShellComponent],
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AppComponent implements OnInit {
  public title = 'boukii-admin-v5';

  private readonly auth = inject(AuthStore);

  public ngOnInit(): void {
    // For demo purposes, simulate a logged-in user after 2 seconds
    // In real app, this would happen after successful login
    setTimeout(() => {
      this.simulateLogin();
    }, 2000);
  }

  private simulateLogin(): void {
    // Mock user data for demo purposes
    const _mockUser = {
      id: 1,
      name: 'Juan Pérez',
      email: 'juan.perez@boukii.com',
      roles: ['admin', 'user'],
      avatar: '', // No avatar, will show initials
    };

    const mockToken = 'mock-jwt-token-for-demo';

    // Simulate setting the auth state (in real app this would come from login response)
    if (typeof localStorage !== 'undefined') {
      localStorage.setItem('auth_token', mockToken);
    }

    // Manually set the auth state for demo
    // Note: In real app, this would be done through auth.signIn() method
    // But for demo purposes, we'll directly patch the state

    // For now, just log that we would set the user
    // console.log('Demo: Would set user to authenticated state', mockUser);

    // In a real implementation, the AuthStore.loadMe() would handle this
    // when it gets a successful response from /api/auth/me
  }
}
