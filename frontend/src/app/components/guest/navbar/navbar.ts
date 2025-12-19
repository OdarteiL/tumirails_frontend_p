import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink, RouterLinkActive } from '@angular/router';
import { LucideAngularModule,Menu,X } from 'lucide-angular';

@Component({
  selector: 'app-navbar',
  standalone: true,
  imports: [CommonModule, RouterLink, RouterLinkActive, LucideAngularModule],
  templateUrl: './navbar.html',
  styleUrl: './navbar.css'
})
export class NavbarComponent {
  isMenuOpen = false;
  readonly Menu = Menu;
  readonly X = X;

  toggleMenu() {
    this.isMenuOpen = !this.isMenuOpen;
  }
}
