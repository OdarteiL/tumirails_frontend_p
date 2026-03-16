import { Component, HostListener, ViewChild, ElementRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink, RouterLinkActive, Router, NavigationEnd } from '@angular/router';
import { LucideAngularModule,Menu,X,ChevronDown } from 'lucide-angular';
import { filter } from 'rxjs/operators';

@Component({
  selector: 'app-navbar',
  standalone: true,
  imports: [CommonModule, RouterLink, RouterLinkActive, LucideAngularModule],
  templateUrl: './navbar.html',
  styleUrl: './navbar.css'
})
export class NavbarComponent {
  isMenuOpen = false;
  isServicesDropdownOpen = false;
  isScrolled = false;
  isHomePage = false;
  readonly Menu = Menu;
  readonly X = X;
  readonly ChevronDown = ChevronDown;

  @ViewChild('servicesDropdown') servicesDropdown?: ElementRef;

  constructor(private router: Router) {
    this.router.events.pipe(filter(e => e instanceof NavigationEnd)).subscribe((e: NavigationEnd) => {
      this.isHomePage = e.urlAfterRedirects === '/';
    });
  }

  @HostListener('window:scroll')
  onScroll() {
    this.isScrolled = window.scrollY > 10;
  }

  get isTransparent(): boolean {
    return this.isHomePage && !this.isScrolled;
  }

  toggleMenu() {
    this.isMenuOpen = !this.isMenuOpen;
  }

  toggleServicesDropdown() {
    this.isServicesDropdownOpen = !this.isServicesDropdownOpen;
  }

  @HostListener('document:click', ['$event'])
  onDocumentClick(event: MouseEvent) {
    if (this.servicesDropdown && !this.servicesDropdown.nativeElement.contains(event.target)) {
      this.isServicesDropdownOpen = false;
    }
  }
}
