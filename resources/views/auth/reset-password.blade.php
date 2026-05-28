<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Reset Password - Country Yoghurt MD</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
    <link rel="stylesheet" href="{{ asset('assets/css/login.css') }}" />
    <link rel="icon" type="image/png" href="{{ asset('assets/img/logo.png') }}" />
  </head>
  <body>
    <div class="login-shell">
      <div class="lp-left">
        <div class="lp-slider" aria-hidden="true">
          <div class="lp-slide lp-slide-active" style="background:linear-gradient(135deg,#1a0550 0%,#0d3d2b 100%)"></div>
          <div class="lp-slider-overlay"></div>
        </div>
        <div class="lp-brand">
          <div class="lp-brand-icon"><img src="{{ asset('assets/img/logo.png') }}" alt="Country Yoghurt" /></div>
          <div><h1>Country Yoghurt</h1><p>Manufacturing & Distribution</p></div>
        </div>
        <div class="lp-hero">
          <h2>Set New<br><span>Password</span></h2>
          <p>Choose a strong password to keep your account secure.</p>
        </div>
      </div>

      <div class="lp-right">
        <div class="lp-form-wrap">
          <div class="lp-form-head">
            <h2>Reset Password</h2>
            <p>Enter and confirm your new password below.</p>
          </div>

          @if ($errors->any())
            <div class="lp-error"><i class="bi bi-exclamation-circle"></i> {{ $errors->first() }}</div>
          @endif

          <form method="POST" action="{{ route('password.update') }}" class="lp-form" novalidate>
            @csrf
            <input type="hidden" name="token" value="{{ $token }}" />

            <div class="form-group">
              <label for="password">New Password</label>
              <div class="input-wrap">
                <i class="bi bi-lock"></i>
                <input id="password" type="password" name="password"
                       placeholder="Min. 8 characters" autocomplete="new-password" required />
                <button type="button" class="pw-toggle" aria-label="Toggle">
                  <i class="bi bi-eye" id="pwEyeIcon"></i>
                </button>
              </div>
            </div>

            <div class="form-group">
              <label for="password_confirmation">Confirm Password</label>
              <div class="input-wrap">
                <i class="bi bi-lock-fill"></i>
                <input id="password_confirmation" type="password" name="password_confirmation"
                       placeholder="Re-enter password" autocomplete="new-password" required />
              </div>
            </div>

            <button type="submit" class="btn-signin">Update Password</button>
          </form>

          <p class="lp-footer-note">Country Yoghurt &copy; {{ date('Y') }}</p>
        </div>
      </div>
    </div>

    <script>
      const pwToggle = document.querySelector('.pw-toggle');
      const pwInput  = document.getElementById('password');
      const pwIcon   = document.getElementById('pwEyeIcon');
      pwToggle.addEventListener('click', () => {
        const hidden     = pwInput.type === 'password';
        pwInput.type     = hidden ? 'text' : 'password';
        pwIcon.className = hidden ? 'bi bi-eye-slash' : 'bi bi-eye';
      });
    </script>
  </body>
</html>
