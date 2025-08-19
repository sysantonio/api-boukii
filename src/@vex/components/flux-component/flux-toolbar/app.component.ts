import { Component, EventEmitter, Input, Output, } from '@angular/core';

@Component({
  selector: 'vex-flux-toolbad',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.scss']
})
export class FluxToolbarComponent {
  @Input() Modal!: { Name: string, Modal: number }[]
  @Input() Flux: number = 0
  @Input() create: boolean = false

  @Output() Check = new EventEmitter<number>()
}
