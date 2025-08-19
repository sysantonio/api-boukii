import { Component, Input } from '@angular/core';
import { LayoutService } from 'src/@vex/services/layout.service';

@Component({
  selector: 'vex-icon',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.scss'],
  standalone: true
})
export class IconComponent {
  constructor(public LayoutService: LayoutService) { }
  @Input() src: string = ""
  @Input() size: number = 24
  @Input() href: string = ""
}
