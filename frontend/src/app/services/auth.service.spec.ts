import { TestBed } from '@angular/core/testing';
import { AuthService } from './auth.service';
import { ApiService } from './api.service';
import { of, throwError } from 'rxjs';
import { User, AuthResponse, LoginRequest, RegisterRequest } from '../models/user.model';

describe('AuthService', () => {
    let service: AuthService;
    let apiServiceSpy: jasmine.SpyObj<ApiService>;

    const mockUser: User = {
        id: 1,
        first_name: 'Test',
        last_name: 'User',
        email: 'test@example.com',
        role: 'customer',
        status: 'active',
        created_at: '2025-01-01T00:00:00Z',
        updated_at: '2025-01-01T00:00:00Z'
    };

    const mockAuthResponse: AuthResponse = {
        success: true,
        message: 'Success',
        data: {
            user: mockUser,
            access_token: 'test-token'
        }
    };

    beforeEach(() => {
        const spy = jasmine.createSpyObj('ApiService', ['post', 'get']);

        // Clear localStorage before each test
        localStorage.clear();

        TestBed.configureTestingModule({
            providers: [
                AuthService,
                { provide: ApiService, useValue: spy }
            ]
        });

        service = TestBed.inject(AuthService);
        apiServiceSpy = TestBed.inject(ApiService) as jasmine.SpyObj<ApiService>;
    });

    it('should be created', () => {
        expect(service).toBeTruthy();
    });

    describe('login', () => {
        it('should login user and update state', (done) => {
            const loginRequest: LoginRequest = { email: 'test@example.com', password: 'password' };
            apiServiceSpy.post.and.returnValue(of(mockAuthResponse));

            service.login(loginRequest).subscribe(response => {
                expect(response).toEqual(mockAuthResponse);
                expect(service.isAuthenticated()).toBeTrue();
                expect(service.currentUser()).toEqual(mockUser);
                expect(localStorage.getItem('access_token')).toBe('test-token');
                done();
            });
        });

        it('should handle login error', (done) => {
            const loginRequest: LoginRequest = { email: 'test@example.com', password: 'wrong' };
            const errorResponse = { error: { message: 'Invalid credentials' } };
            apiServiceSpy.post.and.returnValue(throwError(() => errorResponse));

            service.login(loginRequest).subscribe({
                error: (error) => {
                    expect(error).toEqual(errorResponse);
                    expect(service.isAuthenticated()).toBeFalse();
                    done();
                }
            });
        });
    });

    describe('register', () => {
        it('should register user and update state', (done) => {
            const registerRequest: RegisterRequest = {
                first_name: 'Test',
                last_name: 'User',
                email: 'test@example.com',
                password: 'password',
                password_confirmation: 'password',
                role: 'customer'
            };

            apiServiceSpy.post.and.returnValue(of(mockAuthResponse));

            service.register(registerRequest).subscribe(response => {
                expect(response).toEqual(mockAuthResponse);
                expect(service.isAuthenticated()).toBeTrue();
                expect(localStorage.getItem('access_token')).toBe('test-token');
                done();
            });
        });
    });

    describe('logout', () => {
        it('should logout user and clear state', (done) => {
            // Set initial state
            localStorage.setItem('access_token', 'token');
            localStorage.setItem('user', JSON.stringify(mockUser));

            // Re-initialize to pick up state (or manually set it if methods exposed)
            // Since initializeAuth is private and called in constructor, we might need a workaround 
            // or just assume we can set internal state if we could. 
            // However, for this test let's rely on the public method clearing everything.

            apiServiceSpy.post.and.returnValue(of({ success: true, message: 'Logged out' }));

            service.logout().subscribe(() => {
                expect(service.isAuthenticated()).toBeFalse();
                expect(service.currentUser()).toBeNull();
                expect(localStorage.getItem('access_token')).toBeNull();
                done();
            });
        });
    });
});
