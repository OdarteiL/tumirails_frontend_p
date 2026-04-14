import { Component } from '@angular/core';
import { RouterModule } from '@angular/router';
import { LucideAngularModule, LayoutDashboard, Users, Building2, Mail, Package, Settings } from 'lucide-angular';

@Component({
  selector: 'app-admin-nav',
  standalone: true,
  imports: [RouterModule, LucideAngularModule],
  styles: [`
    a {
      display: flex;
      align-items: center;
      gap: 1rem;
      padding: 0.875rem 1.25rem;
      border-radius: 12px;
      color: rgba(255,255,255,0.6);
      text-decoration: none;
      transition: all 0.3s ease;
      margin-bottom: 0.5rem;
      font-weight: 500;
      font-size: 0.95rem;
    }
    a:hover {
      background-color: rgba(255,255,255,0.05);
      color: white;
      transform: translateX(4px);
    }
    a.active {
      color: var(--color-secondary, #8be0ff);
      background-color: rgba(139,224,255,0.1);
      box-shadow: inset 0 0 0 1px rgba(139,224,255,0.2);
    }
  `],
  template: `
    <a routerLink="/admin/dashboard" routerLinkActive="active" [routerLinkActiveOptions]="{exact:true}">
      <lucide-icon [img]="LayoutDashboard" [size]="20"></lucide-icon>
      <span>Dashboard</span>
    </a>
    <a routerLink="/admin/users" routerLinkActive="active">
      <lucide-icon [img]="Users" [size]="20"></lucide-icon>
      <span>Users</span>
    </a>
    <a routerLink="/admin/organisations" routerLinkActive="active">
      <lucide-icon [img]="Building2" [size]="20"></lucide-icon>
      <span>Organisations</span>
    </a>
    <a routerLink="/admin/contacts" routerLinkActive="active">
      <lucide-icon [img]="Mail" [size]="20"></lucide-icon>
      <span>Enquiries</span>
    </a>
    <a routerLink="/admin/appliances" routerLinkActive="active">
      <lucide-icon [img]="Package" [size]="20"></lucide-icon>
      <span>Appliances</span>
    </a>
    <a routerLink="/admin/settings" routerLinkActive="active">
      <lucide-icon [img]="Settings" [size]="20"></lucide-icon>
      <span>Settings</span>
    </a>
  `
})
export class AdminNavComponent {
  readonly LayoutDashboard = LayoutDashboard;
  readonly Users = Users;
  readonly Building2 = Building2;
  readonly Mail = Mail;
  readonly Package = Package;
  readonly Settings = Settings;
}
