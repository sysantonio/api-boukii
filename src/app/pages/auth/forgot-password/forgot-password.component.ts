import { Component, OnInit } from '@angular/core';
import { UntypedFormBuilder, Validators } from '@angular/forms';
import { MatSnackBar } from '@angular/material/snack-bar';
import { Router } from '@angular/router';
import { TranslateService } from '@ngx-translate/core';
import { fadeInUp400ms } from 'src/@vex/animations/fade-in-up.animation';
import { ApiCrudService } from 'src/service/crud.service';

@Component({
  selector: 'vex-forgot-password',
  templateUrl: './forgot-password.component.html',
  styleUrls: ['./forgot-password.component.scss'],
  animations: [fadeInUp400ms]
})
export class ForgotPasswordComponent implements OnInit {

  form = this.fb.group({
    email: [null, Validators.required]
  });

  constructor(
    private router: Router,
    private fb: UntypedFormBuilder,
    private crudService: ApiCrudService,
    private snackbar: MatSnackBar,
    private translateService: TranslateService
  ) { }

  ngOnInit() { }

  send() {
    this.crudService.login('/forgot-password', {email: this.form.value.email, type: '1'})
      .subscribe((res) => {
        this.snackbar.open(this.translateService.instant('forgot_message'), 'OK', {duration: 3000})
        this.router.navigate(['/login']);

      })
  }
}
