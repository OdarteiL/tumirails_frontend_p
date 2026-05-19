import { Component, signal, input, inject, HostListener } from '@angular/core';
import { Router, RouterModule } from '@angular/router';
import {
    LucideAngularModule,
    Menu,
    X,
    ChevronDown,
    Bell,
    Search,
    LogOut
} from 'lucide-angular';
import { CommonModule } from '@angular/common';
import { AuthService } from '../../../services/auth.service';

@Component({
    selector: 'app-admin-layout',
    standalone: true,
    imports: [
        CommonModule,
        LucideAngularModule,
        RouterModule
    ],
    templateUrl: './admin-layout.html',
    styleUrl: './admin-layout.css'
})
export class AdminLayoutComponent {
    private authService = inject(AuthService) as AuthService;
    private router = inject(Router);

    // Inputs for common layout properties
    pageTitle = input<string>('Admin Panel');
    userName = input<string>('John Smith');
    userRole = input<string>('Administrator');
    userInitials = input<string>('JS');

    isSidebarOpen = signal(false);
    isProfileDropdownOpen = signal(false);

    // Icons for template
    readonly Menu = Menu;
    readonly X = X;
    readonly ChevronDown = ChevronDown;
    readonly Bell = Bell;
    readonly Search = Search;
    readonly LogOut = LogOut;

    toggleSidebar() {
        this.isSidebarOpen.update(v => !v);
    }

    toggleProfileDropdown() {
        this.isProfileDropdownOpen.update(v => !v);
    }

    @HostListener('document:click', ['$event'])
    onDocumentClick(event: MouseEvent) {
        const target = event.target as HTMLElement;
        if (!target.closest('.user-profile')) {
            this.isProfileDropdownOpen.set(false);
        }
    }

    get settingsLink(): string {
        const user = this.authService.currentUser();
        if (!user) return '/customer/settings';
        switch (user.role) {
            case 'admin': return '/admin/settings';
            case 'provider': return '/vendor/settings';
            case 'installer': return '/installer/settings';
            default: return '/customer/settings';
        }
    }

    logout(event: Event) {
        event.preventDefault();
        this.authService.logout().subscribe({
            next: () => this.router.navigate(['/login']),
            error: () => this.router.navigate(['/login'])
        });
    }
}
