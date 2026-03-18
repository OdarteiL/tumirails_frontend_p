<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterInstallerRequest;
use App\Http\Requests\RegisterProviderRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Resources\InstallerDetailResource;
use App\Http\Resources\ProviderDetailResource;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Registration successful',
            'data' => [
                'user' => new UserResource($result['user']),
                'access_token' => $result['access_token'],
            ],
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->validated());

        if (! $result) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid credentials',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => new UserResource($result['user']),
                'access_token' => $result['access_token'],
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json([
            'success' => true,
            'message' => 'Successfully logged out',
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $this->authService->getAuthenticatedUser($request->user());

        return response()->json([
            'success' => true,
            'message' => 'User retrieved successfully',
            'data' => [
                'user' => new UserResource($user),
            ],
        ]);
    }

    public function registerInstaller(RegisterInstallerRequest $request): JsonResponse
    {
        $result = $this->authService->registerInstaller($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Installer registration successful',
            'data' => [
                'user' => new UserResource($result['user']),
                'installer' => new InstallerDetailResource($result['installer']),
                'access_token' => $result['access_token'],
            ],
        ], 201);
    }

    public function registerProvider(RegisterProviderRequest $request): JsonResponse
    {
        $result = $this->authService->registerProvider($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Provider registration successful',
            'data' => [
                'user' => new UserResource($result['user']),
                'provider' => new ProviderDetailResource($result['provider']),
                'access_token' => $result['access_token'],
            ],
        ], 201);
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $this->authService->forgotPassword($request->validated('email'));

        return response()->json([
            'success' => true,
            'message' => 'If an account with that email exists, a password reset link has been sent.',
        ]);
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $data = $request->validated();
        $success = $this->authService->resetPassword($data['email'], $data['token'], $data['password']);

        if (! $success) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid or expired password reset token.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Password has been reset successfully.',
        ]);
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $data = $request->validated();
        $success = $this->authService->changePassword($request->user(), $data['current_password'], $data['password']);

        if (! $success) {
            return response()->json([
                'success' => false,
                'error' => 'Current password is incorrect.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully.',
        ]);
    }
}
