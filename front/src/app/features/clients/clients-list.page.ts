import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormBuilder } from '@angular/forms';
import { ActivatedRoute, Router, convertToParamMap } from '@angular/router';
import { TranslatePipe } from '@shared/pipes/translate.pipe';
import { ClientsV5Service, Client, GetClientsParams } from '@core/services/clients-v5.service';
import { ContextService } from '@core/services/context.service';

@Component({
  selector: 'app-clients-list-page',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, TranslatePipe],
  template: `
    <div class="page">
      <div class="page-header">
        <h1>{{ 'clients.title' | translate }}</h1>
      </div>

      <form [formGroup]="filtersForm" class="filters">
        <input type="text" formControlName="q" placeholder="Search" />
        <input type="number" formControlName="sport_id" placeholder="Sport ID" />
        <select formControlName="active">
          <option value="">All</option>
          <option value="true">Active</option>
          <option value="false">Inactive</option>
        </select>
      </form>

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
          <tr *ngFor="let client of clients">
            <td>{{ client.fullName }}</td>
            <td>{{ client.email }}</td>
            <td>{{ client.phone }}</td>
            <td>{{ client.utilizadores }}</td>
            <td>{{ client.sportsSummary }}</td>
            <td>{{ client.status }}</td>
            <td>{{ client.signupDate }}</td>
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
      form.filters {
        margin-bottom: 1rem;
        display: flex;
        gap: 0.5rem;
      }
    `,
  ],
})
export class ClientsListPageComponent implements OnInit {
  private readonly fb = inject(FormBuilder);
  private readonly route = inject(ActivatedRoute);
  private readonly router = inject(Router);
  private readonly clientsService = inject(ClientsV5Service);
  private readonly contextService = inject(ContextService);

  filtersForm = this.fb.group({
    q: [''],
    sport_id: [''],
    active: [''],
  });

  clients: Client[] = [];

  ngOnInit(): void {
    const params = this.route.snapshot.queryParamMap;
    this.filtersForm.patchValue(
      {
        q: params.get('q') || '',
        sport_id: params.get('sport_id') || '',
        active: params.get('active') || '',
      },
      { emitEvent: false }
    );

    this.loadClients();

    this.filtersForm.valueChanges.subscribe(() => {
      this.updateQueryParams();
      this.loadClients();
    });
  }

  private updateQueryParams(): void {
    const value = this.filtersForm.value;
    const queryParams: any = {};
    if (value.q) queryParams.q = value.q;
    if (value.sport_id) queryParams.sport_id = value.sport_id;
    if (value.active) queryParams.active = value.active;
    this.router.navigate([], {
      queryParams,
      queryParamsHandling: 'merge',
    });
  }

  private loadClients(): void {
    const value = this.filtersForm.value;
    const params: GetClientsParams = {
      school_id: this.contextService.schoolId() || 0,
      q: value.q || undefined,
      sport_id: value.sport_id ? Number(value.sport_id) : undefined,
      active: value.active !== '' ? value.active === 'true' : undefined,
    };
    this.clientsService.getClients(params).subscribe((res) => {
      this.clients = res.data;
    });
  }
}

