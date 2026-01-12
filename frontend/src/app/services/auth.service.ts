import { Injectable, signal } from '@angular/core';
import { Observable, BehaviorSubject, tap, catchError, throwError } from 'rxjs';
import { ApiService } from './api.service';
import { User, AuthResponse, LoginRequest, RegisterRequest, RegisterInstallerRequest, RegisterProviderRequest } from '../models/user.model';

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private currentUserSubject = new BehaviorSubject<User | null>(null);
  public currentUser$ = this.currentUserSubject.asObservable();

  // Angular signals for reactive state
  public isAuthenticated = signal<boolean>(false);
  public currentUser = signal<User | null>(null);

  constructor(private apiService: ApiService) {
    this.initializeAuth();
  }

  private initializeAuth(): void {
    const token = localStorage.getItem('access_token');
    const user = localStorage.getItem('user');

    if (token && user) {
      const parsedUser = JSON.parse(user);
      this.setAuthState(parsedUser, token);
    }
  }

  private setAuthState(user: User, token: string): void {
    localStorage.setItem('access_token', token);
    localStorage.setItem('user', JSON.stringify(user));

    this.currentUserSubject.next(user);
    this.currentUser.set(user);
    this.isAuthenticated.set(true);
  }

  private clearAuthState(): void {
    localStorage.removeItem('access_token');
    localStorage.removeItem('user');

    this.currentUserSubject.next(null);
    this.currentUser.set(null);
    this.isAuthenticated.set(false);
  }

  register(data: RegisterRequest): Observable<AuthResponse> {
    return this.apiService.post<AuthResponse>('/auth/register', data).pipe(
      tap(response => this.setAuthState(response.data.user, response.data.access_token)),
      catchError(error => throwError(() => error))
    );
  }

  login(credentials: LoginRequest): Observable<AuthResponse> {
    return this.apiService.post<AuthResponse>('/auth/login', credentials).pipe(
      tap(response => this.setAuthState(response.data.user, response.data.access_token)),
      catchError(error => throwError(() => error))
    );
  }

  logout(): Observable<{ success: boolean; message: string }> {
    return this.apiService.post<{ success: boolean; message: string }>('/auth/logout', {}).pipe(
      tap(() => this.clearAuthState()),
      catchError(error => {
        // Clear auth state even if logout fails
        this.clearAuthState();
        return throwError(() => error);
      })
    );
  }

  getCurrentUser(): Observable<{ user: User }> {
    return this.apiService.get<{ user: User }>('/auth/me');
  }

  registerInstaller(data: RegisterInstallerRequest): Observable<AuthResponse> {
    return this.apiService.post<AuthResponse>('/auth/register/installer', data).pipe(
      tap(response => this.setAuthState(response.data.user, response.data.access_token)),
      catchError(error => throwError(() => error))
    );
  }

  registerProvider(data: RegisterProviderRequest): Observable<AuthResponse> {
    return this.apiService.post<AuthResponse>('/auth/register/provider', data).pipe(
      tap(response => this.setAuthState(response.data.user, response.data.access_token)),
      catchError(error => throwError(() => error))
    );
  }
}