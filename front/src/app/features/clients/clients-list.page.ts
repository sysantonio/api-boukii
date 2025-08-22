import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { TranslatePipe } from '@shared/pipes/translate.pipe';

@Component({
  selector: 'app-clients-list-page',
  standalone: true,
  imports: [CommonModule, TranslatePipe],
  template: `
    <div class="page">
      <div class="page-header">
        <h1>{{ 'clients.title' | translate }}</h1>
      </div>

      <table>
        <thead>
          <tr>
            <th>{{ 'clients.fullName' | translate }}</th>
            <th>{{ 'clients.email' | translate }}</th>
            <th>{{ 'clients.phone' | translate }}</th>
            <th>{{ 'clients.utilizadores' | translate }}</th>
            <th>{{ 'clients.sportsSummary' | translate }}</th>
            <th>{{ 'clients.status' | translate }}</th>
            <th>{{ 'clients.signupDate' | translate }}</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>John Doe</td>
            <td>john@example.com</td>
            <td>555-1234</td>
            <td>0</td>
            <td>Surf, Yoga</td>
            <td>Active</td>
            <td>2024-01-01</td>
          </tr>
        </tbody>
      </table>
    </div>
  `,
  styles: [
    `
      table {
        width: 100%;
      }
      th, td {
        padding: 8px;
        text-align: left;
      }
    `,
  ],
})
export class ClientsListPageComponent {}

