<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers {
        login as traitLogin;
        showLoginForm as traitShowLoginForm;
    }

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    public function showLoginForm(Request $request)
    {
        $request->session()->forget('url.intended');

        return $this->traitShowLoginForm();
    }

    public function login(Request $request)
    {
        $request->session()->forget('url.intended');

        return $this->traitLogin($request);
    }

    protected function authenticated(Request $request, $user)
    {
        if ($user->isActive()) {
            if ($user->requiresPasswordChange()) {
                return redirect()
                    ->route('password.change')
                    ->with('warning', 'Por seguridad debes cambiar tu contraseña antes de continuar.');
            }

            return redirect()->route('home');
        }

        $this->guard()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        throw ValidationException::withMessages([
            $this->username() => ['Usuario inactivo. Contacte al administrador.'],
        ]);
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        $this->logoutCurrentUser($request, true);

        return $this->withBrowserStateResetHeaders(redirect()->route('login'));
    }

    private function withBrowserStateResetHeaders($response)
    {
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', 'Fri, 01 Jan 1990 00:00:00 GMT');
        $response->headers->set('Clear-Site-Data', '"cache", "storage"');

        return $response;
    }

    private function logoutCurrentUser(Request $request, bool $forceInvalidate): void
    {
        $hadAuthenticatedUser = $this->guard()->check();

        if ($hadAuthenticatedUser) {
            $this->guard()->logout();
        }

        if (($forceInvalidate || $hadAuthenticatedUser) && $request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }
    }
}
