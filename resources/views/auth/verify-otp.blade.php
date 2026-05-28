<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Verify OTP - Country Yoghurt MD</title>
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
          <h2>Enter Your<br><span>OTP Code</span></h2>
          <p>Check your phone for the 6-digit code we sent you.</p>
        </div>
      </div>

      <div class="lp-right">
        <div class="lp-form-wrap">
          <a href="{{ route('password.request') }}" class="back-link" style="display:inline-flex;align-items:center;gap:6px;font-size:.78rem;font-weight:500;color:#1d086c;text-decoration:none;margin-bottom:22px;">
            <i class="bi bi-arrow-left"></i> Back
          </a>
          <div class="lp-form-head">
            <h2>Verify OTP</h2>
            <p>Enter the 6-digit code sent to your phone.</p>
          </div>

          @if (session('status'))
            <div class="lp-success"><i class="bi bi-check-circle"></i> {{ session('status') }}</div>
          @endif
          @if (session('dev_otp'))
            <div class="lp-success"><i class="bi bi-info-circle"></i> {{ session('dev_otp') }}</div>
          @endif
          @if ($errors->any())
            <div class="lp-error"><i class="bi bi-exclamation-circle"></i> {{ $errors->first() }}</div>
          @endif

          <form method="POST" action="{{ route('password.verify.post') }}" class="lp-form" novalidate>
            @csrf
            <div class="form-group">
              <label for="otp">One-Time Password</label>
              <div class="input-wrap">
                <i class="bi bi-key"></i>
                <input id="otp" type="text" name="otp" placeholder="6-digit code"
                       maxlength="6" inputmode="numeric" pattern="\d{6}" autofocus required />
              </div>
            </div>
            <button type="submit" class="btn-signin">Verify & Continue</button>
          </form>

          <p class="lp-footer-note">Country Yoghurt &copy; {{ date('Y') }}</p>
        </div>
      </div>
    </div>
  </body>
</html>
