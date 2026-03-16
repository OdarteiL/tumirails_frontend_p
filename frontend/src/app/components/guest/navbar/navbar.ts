import { Component, HostListener, ViewChild, ElementRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink, RouterLinkActive } from '@angular/router';
import { LucideAngularModule,Menu,X,ChevronDown } from 'lucide-angular';

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
  readonly Menu = Menu;
  readonly X = X;
  readonly ChevronDown = ChevronDown;

  @ViewChild('servicesDropdown') servicesDropdown?: ElementRef;

  @HostListener('window:scroll')
  onScroll() {
    this.isScrolled = window.scrollY > 10;
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
