<?php $__env->startSection('auth', true); ?>

<?php $__env->startPush('styles'); ?>
    <style>
        #auth {
            min-height: 100vh;
            overflow: hidden;
            background: #151521;
        }

        #auth .row {
            min-height: 100vh;
        }

        #auth .siga-login-side {
            position: relative;
            z-index: 2;
            background: #151521;
        }

        #auth .siga-login-side::after {
            content: "";
            position: absolute;
            top: 0;
            right: -8.5vw;
            z-index: -1;
            width: 17vw;
            height: 100%;
            background: #151521;
            transform: skewX(-12deg);
            transform-origin: top;
        }

        #auth #auth-left {
            display: flex;
            align-items: center;
            min-height: 100vh;
            padding: 3rem 3.25rem;
        }

        #auth .siga-login-form {
            width: 100%;
            max-width: 390px;
        }

        #auth .siga-mobile-entry {
            max-width: 390px;
            margin-top: 1.5rem;
            padding: 1rem;
            border: 1px solid rgba(67, 94, 190, .35);
            border-radius: .45rem;
            background: rgba(16, 24, 40, .45);
        }

        #auth .siga-mobile-entry strong {
            display: block;
            margin-bottom: .25rem;
            color: #ffffff;
            font-size: .98rem;
        }

        #auth .siga-mobile-entry p {
            margin-bottom: .8rem;
            color: #b8bdd8;
            font-size: .88rem;
            line-height: 1.35;
        }

        #auth .form-group {
            margin-bottom: 1rem !important;
        }

        #auth .form-control.form-control-xl {
            min-height: 50px;
            padding: .7rem 1.15rem .7rem 3.75rem;
            color: #f7f8ff;
            background: #1f1e2e;
            border: 1px solid #3d3d58;
            border-radius: 999px;
            font-size: .98rem;
        }

        #auth .form-control.form-control-xl::placeholder {
            color: #c8cce0;
        }

        #auth .form-control.form-control-xl:focus {
            color: #ffffff;
            background: #1f1e2e;
            border-color: #435ebe;
            box-shadow: 0 0 0 .2rem rgba(67, 94, 190, .22);
        }

        #auth .form-group.has-password-toggle .form-control.form-control-xl {
            padding-right: 3.25rem;
        }

        #auth .form-control-icon {
            left: 1.05rem;
            top: 50%;
            width: 1.35rem;
            height: auto;
            transform: translateY(-50%);
            font-size: 1.05rem;
            text-align: center;
            pointer-events: none;
        }

        #auth .form-control-icon i {
            line-height: 1;
        }

        #auth .password-toggle {
            position: absolute;
            right: .95rem;
            top: 50%;
            z-index: 3;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2rem;
            height: 2rem;
            padding: 0;
            color: #b9bdd6;
            background: transparent;
            border: 0;
            transform: translateY(-50%);
        }

        #auth .password-toggle:hover,
        #auth .password-toggle:focus {
            color: #ffffff;
            outline: none;
        }

        #auth .password-toggle i {
            font-size: 1.1rem;
            line-height: 1;
        }

        #auth .form-check-lg {
            min-height: auto;
            margin-top: 1.15rem;
        }

        #auth .form-check-lg .form-check-input {
            width: 1.05rem;
            height: 1.05rem;
            margin-top: 0;
        }

        #auth .form-check-label {
            font-size: .88rem;
        }

        #auth .btn-lg {
            min-height: 44px;
            padding: .55rem 1.75rem;
            border-radius: 999px;
            font-size: .98rem;
            font-weight: 600;
        }

        #auth .siga-login-actions {
            display: flex;
            align-items: center;
            gap: 2rem;
            margin-top: 3rem;
        }

        #auth .siga-login-actions .btn {
            flex: 0 0 150px;
        }

        #auth .siga-login-actions a {
            color: #dfe4ff;
            font-weight: 600;
            text-decoration: none;
        }

        #auth .siga-login-actions a:hover,
        #auth .siga-login-actions a:focus {
            color: #ffffff;
            text-decoration: underline;
        }

        #auth #auth-right {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 4rem;
            overflow: hidden;
            background: #435ebe;
        }

        #auth .siga-login-brand {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            min-height: 100%;
        }

        #auth .siga-login-brand img {
            display: block;
            width: 100%;
            height: 100%;
            max-width: none;
            max-height: none;
            object-fit: cover;
            object-position: center;
            filter: none;
        }

        #auth .siga-login-brand-placeholder {
            display: none;
        }

        @media screen and (max-width: 1399.9px) {
            #auth #auth-left {
                padding: 2.5rem 3rem;
            }

        }

        @media screen and (max-width: 767px) {
            #auth .siga-login-side::after {
                display: none;
            }

            #auth #auth-left {
                min-height: 100vh;
                padding: 2.25rem 1.5rem;
            }

            #auth .siga-login-form {
                max-width: none;
            }

            #auth .siga-login-actions {
                align-items: stretch;
                flex-direction: column;
                gap: 1rem;
            }

            #auth .siga-login-actions .btn {
                flex-basis: auto;
            }
        }
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<?php
    $loginAjuste = \App\Models\Ajuste::query()->first();
    $loginImageUrl = $loginAjuste?->imagen_login
        ? asset('storage/' . $loginAjuste->imagen_login)
        : null;
?>

<div id="auth">
    <div class="row h-100">
        <div class="col-lg-5 col-12 siga-login-side">
            <div id="auth-left">
                <div>
                    <form method="POST" action="<?php echo e(route('login')); ?>" class="siga-login-form">
                        <?php echo csrf_field(); ?>

                        <div class="form-group position-relative has-icon-left mb-4">
                            <input id="email" type="email" class="form-control form-control-xl <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" name="email" value="<?php echo e(old('email')); ?>" required autocomplete="email" autofocus placeholder="Correo electronico">
                            <div class="form-control-icon">
                                <i class="bi bi-person"></i>
                            </div>
                            <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <span class="invalid-feedback d-block" role="alert"><strong><?php echo e($message); ?></strong></span>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="form-group position-relative has-icon-left has-password-toggle mb-4">
                            <input id="password" type="password" class="form-control form-control-xl <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" name="password" required autocomplete="current-password" placeholder="Contrasena">
                            <div class="form-control-icon">
                                <i class="bi bi-shield-lock"></i>
                            </div>
                            <button type="button" class="password-toggle" id="togglePassword" aria-label="Mostrar contrasena" aria-pressed="false">
                                <i class="bi bi-eye"></i>
                            </button>
                            <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <span class="invalid-feedback d-block" role="alert"><strong><?php echo e($message); ?></strong></span>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="form-check form-check-lg d-flex align-items-end">
                            <input class="form-check-input me-2" type="checkbox" name="remember" id="remember" <?php echo e(old('remember') ? 'checked' : ''); ?>>
                            <label class="form-check-label text-gray-600" for="remember">Recuerdame</label>
                        </div>

                        <div class="siga-login-actions">
                            <button class="btn btn-primary btn-lg shadow-lg" type="submit">Iniciar sesion</button>
                            <?php if(Route::has('password.request')): ?>
                                <a href="<?php echo e(route('password.request')); ?>">Recuperar contrasena</a>
                            <?php endif; ?>
                        </div>
                    </form>

                    <div class="siga-mobile-entry">
                        <strong><i class="bi bi-phone me-1"></i> SIGA Mobile</strong>
                        <p>Acceso rapido para choferes, mecanicos y supervisores desde el celular.</p>
                        <a href="<?php echo e(route('mobile.app')); ?>" class="btn btn-outline-primary w-100" target="_blank" rel="noopener">
                            Abrir aplicacion movil
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-7 d-none d-lg-block">
            <div id="auth-right">
                <?php if($loginImageUrl): ?>
                    <div class="siga-login-brand">
                        <img src="<?php echo e($loginImageUrl); ?>" alt="<?php echo e($loginAjuste->nombre ?? config('app.name', 'SIGA')); ?>">
                    </div>
                <?php else: ?>
                    <div class="siga-login-brand-placeholder" aria-hidden="true"></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('password');
            const toggleButton = document.getElementById('togglePassword');
            const toggleIcon = toggleButton?.querySelector('i');

            if (!passwordInput || !toggleButton || !toggleIcon) {
                return;
            }

            toggleButton.addEventListener('click', function() {
                const isHidden = passwordInput.type === 'password';

                passwordInput.type = isHidden ? 'text' : 'password';
                toggleButton.setAttribute('aria-label', isHidden ? 'Ocultar contrasena' : 'Mostrar contrasena');
                toggleButton.setAttribute('aria-pressed', isHidden ? 'true' : 'false');
                toggleIcon.classList.toggle('bi-eye', !isHidden);
                toggleIcon.classList.toggle('bi-eye-slash', isHidden);
                passwordInput.focus();
            });
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\wamp64\www\siga\resources\views/auth/login.blade.php ENDPATH**/ ?>