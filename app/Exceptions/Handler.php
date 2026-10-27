<?php

namespace App\Exceptions;

use Illuminate\Database\QueryException;
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
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // MySQL desligado: página explicando o que fazer, em vez da tela técnica do Laravel
        $this->renderable(function (QueryException $e) {
            if ($this->bancoForaDoAr($e)) {
                return response()->view('errors.banco-fora', [], 503);
            }
        });
    }

    /** 2002 = conexão recusada (MySQL parado); 2006 = conexão caiu no meio */
    private function bancoForaDoAr(QueryException $e): bool
    {
        return str_contains($e->getMessage(), '[2002]') || str_contains($e->getMessage(), '[2006]');
    }
}
