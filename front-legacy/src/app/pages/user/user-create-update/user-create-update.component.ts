import { Component, OnInit } from '@angular/core';
import { FormGroup, FormBuilder, Validators, AbstractControl, FormControl } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { fadeInUp400ms } from 'src/@vex/animations/fade-in-up.animation';
import { stagger20ms } from 'src/@vex/animations/stagger.animation';
import { UserService } from 'src/service/userService';

@Component({
  selector: 'vex-user-create-update',
  templateUrl: './user-create-update.component.html',
  styleUrls: ['./user-create-update.component.scss'],
  animations:[stagger20ms, fadeInUp400ms]
})
export class UserCreateUpdateComponent {
  userForm: FormGroup;
  userId: string | null = null;
  mode: string | null = null;
  defaults: any;
  hide = true;
  roles: string[] = ['Administrador', 'Camarero', 'Recepción'];
  selectedRole: string;

  constructor(
    private fb: FormBuilder,
    private route: ActivatedRoute,
    private router: Router
  ) {
    this.userForm = this.fb.group({
      name: ['', Validators.required],
      surname: [''],
      email: ['', [Validators.required, Validators.email]],
      rol: ['', Validators.required],
      password: ['', [Validators.minLength(6), this.passwordValidator]],
      confirmPassword: ['', [Validators.required]]
    }, { validator: this.checkPasswords });
  }

  async ngOnInit() {
    // if you are editing an existing lang, set userId
    // and load the lang details using getLangById()
    this.userId = this.route.snapshot.paramMap.get('id');
    if (this.userId !== null) {

      this.mode = 'update';

      this.defaults = {} as any;


    }
  }

  passwordValidator(formControl: FormControl) {
    const { value } = formControl;
    const hasUpperCase = /[A-Z]/.test(value);
    const hasLowerCase = /[a-z]/.test(value);
    const hasNumeric = /[0-9]/.test(value);
    const hasSpecialChar = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(value);

    if (hasUpperCase && hasLowerCase && hasNumeric && hasSpecialChar) {
      return null;
    } else {
      return { passwordStrength: true };
    }
  }

  checkPasswords(group: FormGroup) {
    let pass = group.controls.password.value;
    let confirmPass = group.controls.confirmPassword.value;

    return pass === confirmPass ? null : { notSame: true }
  }

  save() {

  }

  // Custom Validator
  lengthValidator(control: AbstractControl) {
    const value = control.value;
    const regex = /^[a-zA-Z]{2,5}$/;
    return regex.test(value) ? null : { length: true };
  }

  cancel() {
    this.router.navigate(['/user']); // Navigating to /user on cancel
  }
}
