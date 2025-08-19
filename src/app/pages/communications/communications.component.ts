import { ChangeDetectorRef, Component, DestroyRef, inject } from '@angular/core';
import { NavigationEnd, Router } from '@angular/router';
import { Observable, delay, filter, of, startWith, switchMap } from 'rxjs';
import { trackById } from 'src/@vex/utils/track-by';
import { VexLayoutService } from 'src/service/vex-layout.service';
import { ChatService } from './chat.service';
import { takeUntilDestroyed } from '@angular/core/rxjs-interop';

export interface Chat {
  id: string;
  imageUrl: string;
  name: string;
  lastMessage: string;
  unreadCount: number;
  timestamp: string;
}

export interface ChatMessage {
  id: string;
  from: 'me' | 'partner';
  message: string;
}

@Component({
  selector: 'vex-communications',
  templateUrl: './communications.component.html',
  styleUrls: ['./communications.component.scss']
})
export class CommunicationsComponent {

  chats$: Observable<Chat[]> = of(this.chatService.chats).pipe(
    // Fix to allow stagger animations with static data
    delay(0)
  );

  mobileQuery$ = this.layoutService.ltMd$;
  drawerOpen$ = this.chatService.drawerOpen$;

  trackById = trackById;
  private readonly destroyRef: DestroyRef = inject(DestroyRef);

  constructor(
    private cd: ChangeDetectorRef,
    private router: Router,
    private layoutService: VexLayoutService,
    private chatService: ChatService
  ) {}

  ngOnInit() {
    this.router.events
      .pipe(
        filter((event) => event instanceof NavigationEnd),
        startWith(null),
        switchMap(() => this.mobileQuery$),
        filter((isMobile) => isMobile),
        takeUntilDestroyed(this.destroyRef)
      )
      .subscribe(() => this.closeDrawer());

    this.router.events
      .pipe(
        filter((event) => event instanceof NavigationEnd),
        startWith(null),
        switchMap(() => this.mobileQuery$),
        filter((isMobile) => !isMobile),
        takeUntilDestroyed(this.destroyRef)
      )
      .subscribe(() => this.openDrawer());
  }

  drawerChange(drawerOpen: boolean) {
    this.chatService.drawerOpen.next(drawerOpen);
  }

  openDrawer() {
    this.chatService.drawerOpen.next(true);
    this.cd.markForCheck();
  }

  closeDrawer() {
    this.chatService.drawerOpen.next(false);
    this.cd.markForCheck();
  }
}
