<?php

use App\Http\Middleware\EnsureDeviceCookie;
use App\Http\Middleware\EnsureRecentAdminTwoFactor;
use App\Http\Middleware\ResolveBrand;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Responses\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(prepend: [
            ResolveBrand::class,
        ]);
        $middleware->web(append: [
            EnsureDeviceCookie::class,
            SecurityHeaders::class,
            EnsureRecentAdminTwoFactor::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            'webhook/*',
            'api/revit/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $isApi = static fn (Request $request): bool => $request->is('api/*');

        $exceptions->render(function (AuthenticationException $exception, Request $request) use ($isApi) {
            return $isApi($request) ? ApiResponse::error('Xác thực không hợp lệ hoặc đã hết hạn.', 401) : null;
        });

        $exceptions->render(function (ValidationException $exception, Request $request) use ($isApi) {
            return $isApi($request) ? ApiResponse::error('Dữ liệu gửi lên chưa hợp lệ.', 422, $exception->errors()) : null;
        });

        $exceptions->render(function (ModelNotFoundException $exception, Request $request) use ($isApi) {
            return $isApi($request) ? ApiResponse::error('Không tìm thấy dữ liệu yêu cầu.', 404) : null;
        });

        $exceptions->render(function (HttpExceptionInterface $exception, Request $request) use ($isApi) {
            return $isApi($request) ? ApiResponse::error(
                $exception->getMessage() ?: 'Không thể xử lý yêu cầu.',
                $exception->getStatusCode(),
            ) : null;
        });
    })->create();
