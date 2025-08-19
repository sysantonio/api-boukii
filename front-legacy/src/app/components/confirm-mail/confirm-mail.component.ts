import {Component, Input} from '@angular/core';
import {map} from 'rxjs/operators';
import {ConfigService} from '../../../@vex/config/config.service';

@Component({
  selector: 'vex-confirm-mail',
  templateUrl: './confirm-mail.component.html',
  styleUrls: ['./confirm-mail.component.scss']
})
export class ConfirmMailComponent {
  @Input() title: string;
  @Input() type: string;
  @Input() body: string;
  user: any;

  imageUrl$ = this.configService.config$.pipe(map(config => config.sidenav.imageUrl));

  constructor(private configService: ConfigService) {
    this.user = JSON.parse(localStorage.getItem('boukiiUser'));

  }

}
