
import { Injectable } from '@angular/core';
import { ApiCrudService } from './crud.service';
import * as moment from 'moment';
import {Observable, of, tap} from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class SchoolService {

  public schoolSettings: any;
  user: any;
  constructor(private crudService: ApiCrudService) {
    this.user = JSON.parse(localStorage.getItem('boukiiUser'));
  }

  refreshSchoolData() {
    this.crudService.get('/schools/'+this.user.schools[0].id)
      .subscribe((data) => {
        this.schoolSettings = data.data;
      })
  }

  getSchoolData(user = null, forceParam = false) {
    this.user = JSON.parse(localStorage.getItem('boukiiUser'));
    if (this.user && !forceParam) {

      return this.crudService.get('/schools/'+this.user.schools[0].id);
    } else {
      return this.crudService.get('/schools/'+user.schools[0].id);

    }
  }
}

