<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function render($request, Throwable $e)
    {
        try {
        $error_page = parent::render($request, $e);
        $status = (int) $error_page->getStatusCode();
        $e->getMessage() === NULL ? $message = 'No error message specified.' : $message = $e->getMessage();

        if($status === 401) {
            $message = 'Unauthenticated.';
        }
        if($status === 403) {
            $message = 'Forbidden.';
        }

        if($status === 404) {
            $message = 'Resource not found.';
        }
        if($status === 405) {
            $message = 'Not allowed method.';
        }
        if($status === 419) {
            $message = 'You should re-authenticate.';
        }

        if($status !== 404) {
            if($status !== 422) {
              $dd = explode('/', $e->getFile());
              return response()->json([
                      'success' => false,
                      'status' => 'error',
                      'code' => (int) $status,
                      'message' => $message,
                      'location' => end($dd),
                      'line' => $e->getLine()
              ], $status);
            }
        }

        return response()->json([
                'success' => false,
                'status' => 'error',
                'code' => (int) $status,
                'message' => $message,
        ], $status);

        } catch(\Exception $error_errorred) {
            return response()->json([
                    'success' => false,
                    'status' => 'error',
                    'code' => (int) 500,
                    'message' => 'Exception handler itself actually errored with message: '.$error_errorred->getMessage(),
            ], 500);
        }
    }
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            try {
              save_log("Error", basename($e->getFile()).' - '.$e->getMessage(). ' on line ['.$e->getLine().']');
              return false;
            } catch(\Exception $e) {

            }
        });
    }
}
