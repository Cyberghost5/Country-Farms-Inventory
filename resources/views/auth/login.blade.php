<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sign In - Country Yoghurt MD</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
    <link rel="stylesheet" href="{{ asset('assets/css/login.css') }}" />
    <link rel="icon" type="image/png" href="{{ asset('assets/img/logo.png') }}" />
  </head>
  <body>
    <div class="login-shell">

      {{-- LEFT PANEL --}}
      <div class="lp-left">
        <div class="lp-slider" aria-hidden="true">
          <div class="lp-slide lp-slide-active" style="background:linear-gradient(135deg,#1a0550 0%,#0d3d2b 100%)"></div>
          <div class="lp-slide" style="background:linear-gradient(135deg,#0d3d2b 0%,#1a0550 100%)"></div>
          <div class="lp-slide" style="background:linear-gradient(135deg,#2a1060 0%,#0a4a30 100%)"></div>
          <div class="lp-slider-overlay"></div>
        </div>

        <div class="lp-brand">
          <div class="lp-brand-icon">
            <img src="{{ asset('assets/img/logo.png') }}" alt="Country Yoghurt" />
          </div>
          <div>
            <h1>Country Yoghurt</h1>
            <p>Manufacturing & Distribution</p>
          </div>
        </div>

        <div class="lp-badge">
          <i class="bi bi-shield-check"></i>
          Secure Management Portal
        </div>

        <div class="lp-hero">
          <h2>Produce. Verify.<br><span>Distribute.</span></h2>
          <p>
            End-to-end manufacturing and distribution control<br>
            for Country Yoghurt — from production floor<br>
            to distributor doorstep.
          </p>
        </div>

        <div class="lp-stats">
          <div>
            <strong>5</strong>
            <span>User Roles</span>
          </div>
          <div>
            <strong>100%</strong>
            <span>Accountability</span>
          </div>
          <div>
            <strong>Real-time</strong>
            <span>Stock Tracking</span>
          </div>
        </div>
      </div>

      {{-- RIGHT PANEL --}}
      <div class="lp-right">
        <div class="lp-form-wrap">
          <div class="lp-form-head">
            <h2>Welcome back</h2>
            <p>Sign in to access your management portal.</p>
          </div>

          @if (session('status'))
            <div class="lp-success">
              <i class="bi bi-check-circle"></i>
              {{ session('status') }}
            </div>
          @endif

          @if (session('dev_otp'))
            <div class="lp-success">
              <i class="bi bi-info-circle"></i>
              {{ session('dev_otp') }}
            </div>
          @endif

          @if ($errors->any())
            <div class="lp-error">
              <i class="bi bi-exclamation-circle"></i>
              {{ $errors->first() }}
            </div>
          @endif

          <form method="POST" action="{{ route('login.post') }}" class="lp-form" novalidate>
            @csrf

            <div class="form-group">
              <label for="phone">Phone Number</label>
              <div class="input-wrap">
                <i class="bi bi-phone"></i>
                <input id="phone" type="tel" name="phone" placeholder="e.g. 08012345678"
                       value="{{ old('phone') }}" autocomplete="tel" autofocus required />
              </div>
            </div>

            <div class="form-group">
              <label for="password">Password</label>
              <div class="input-wrap">
                <i class="bi bi-lock"></i>
                <input id="password" type="password" name="password"
                       placeholder="••••••••" autocomplete="current-password" required />
                <button type="button" class="pw-toggle" aria-label="Toggle password visibility">
                  <i class="bi bi-eye" id="pwEyeIcon"></i>
                </button>
              </div>
            </div>

            <div class="form-row">
              <label class="check-label">
                <input type="checkbox" name="remember" id="remember" />
                <span class="custom-check"></span>
                Remember me for 30 days
              </label>
              <a href="{{ route('password.request') }}" class="forgot-link">Forgot password?</a>
            </div>

            <button type="submit" class="btn-signin">
              Sign In to Dashboard
            </button>
          </form>

          <p class="lp-footer-note">
            Restricted to authorised personnel only.<br>
            Country Yoghurt &copy; {{ date('Y') }} &middot; Nigeria<br>
            Powered by <a href="https://zeetechfoundation.org" target="_blank" rel="noopener noreferrer">Zee Tech Ventures</a>
          </p>
        </div>
      </div>

    </div>

    <script>
      // Password toggle
      const pwToggle = document.querySelector('.pw-toggle');
      const pwInput  = document.getElementById('password');
      const pwIcon   = document.getElementById('pwEyeIcon');
      pwToggle.addEventListener('click', () => {
        const hidden     = pwInput.type === 'password';
        pwInput.type     = hidden ? 'text' : 'password';
        pwIcon.className = hidden ? 'bi bi-eye-slash' : 'bi bi-eye';
      });

      // Slide transition
      (function () {
        const slides = document.querySelectorAll('.lp-slide');
        let current  = 0;
        setInterval(() => {
          slides[current].classList.remove('lp-slide-active');
          current = (current + 1) % slides.length;
          slides[current].classList.add('lp-slide-active');
        }, 5000);
      })();
    </script>
  </body>
</html>
