<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Create User - Country Yoghurt MD</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}" />
    <link rel="icon" type="image/png" href="{{ asset('assets/img/logo.png') }}" />
  </head>
  <body>
    @include('partials._mobile_topbar')
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>
    <div class="app-shell">
      <aside class="sidebar" id="sidebar">@include('partials._sidebar')</aside>

      <main class="main-content">
        <header class="topbar">
          <div class="title-block">
            <h2>Create {{ ucfirst(str_replace('_', ' ', $forRole)) }}</h2>
            <p>Add a new {{ str_replace('_', ' ', $forRole) }} account.</p>
          </div>
          <div class="top-actions">
            <a href="{{ route('admin.users.index') }}" class="ghost-btn">
              <i class="bi bi-arrow-left"></i> Back
            </a>
          </div>
        </header>

        @if ($errors->any())
          <div class="lp-error" style="margin-bottom:14px;">
            <i class="bi bi-exclamation-circle"></i>
            <ul style="margin:4px 0 0 16px;padding:0;">
              @foreach ($errors->all() as $err)
                <li>{{ $err }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <section class="card" style="padding:28px;">
          <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf

            <div class="form-row-2">
              <div class="form-group">
                <label>Full Name *</label>
                <input class="form-input" type="text" name="name" value="{{ old('name') }}" required />
              </div>
              <div class="form-group">
                <label>Role *</label>
                <select class="form-input" name="role" required>
                  <option value="general_manager"    {{ old('role', $forRole) === 'general_manager'    ? 'selected' : '' }}>General Manager</option>
                  <option value="production_manager" {{ old('role', $forRole) === 'production_manager' ? 'selected' : '' }}>Production Manager</option>
                  <option value="store_manager"      {{ old('role', $forRole) === 'store_manager'      ? 'selected' : '' }}>Store Manager</option>
                  <option value="distributor"        {{ old('role', $forRole) === 'distributor'        ? 'selected' : '' }}>Distributor</option>
                </select>
              </div>
            </div>

            <div class="form-row-2">
              <div class="form-group">
                <label>Phone Number *</label>
                <input class="form-input" type="text" name="phone" value="{{ old('phone') }}"
                       placeholder="e.g. 08012345678" required />
              </div>
              <div class="form-group">
                <label>Email</label>
                <input class="form-input" type="email" name="email" value="{{ old('email') }}"
                       placeholder="Optional" />
              </div>
            </div>

            <div class="form-row-2">
              <div class="form-group">
                <label>Company Name</label>
                <input class="form-input" type="text" name="company_name" value="{{ old('company_name') }}"
                       placeholder="For distributors" />
              </div>
              <div class="form-group">
                <label>State</label>
                <input class="form-input" type="text" name="state" value="{{ old('state') }}"
                       placeholder="e.g. Lagos" />
              </div>
            </div>

            <div class="form-group">
              <label>Address</label>
              <textarea class="form-input" name="address" rows="2"
                        placeholder="Optional delivery/business address">{{ old('address') }}</textarea>
            </div>

            <div class="form-row-2">
              <div class="form-group">
                <label>Password *</label>
                <div class="pw-wrap">
                  <input class="form-input" type="password" name="password" id="pwField" required minlength="8" />
                  <button type="button" class="pw-toggle" onclick="togglePw('pwField','pwEye')">
                    <i class="bi bi-eye" id="pwEye"></i>
                  </button>
                </div>
              </div>
              <div class="form-group">
                <label>Confirm Password *</label>
                <div class="pw-wrap">
                  <input class="form-input" type="password" name="password_confirmation" id="pwConfirm" required minlength="8" />
                  <button type="button" class="pw-toggle" onclick="togglePw('pwConfirm','pwConfirmEye')">
                    <i class="bi bi-eye" id="pwConfirmEye"></i>
                  </button>
                </div>
              </div>
            </div>

            <div style="margin-top:24px;display:flex;gap:12px;justify-content:flex-end;">
              <a href="{{ route('admin.users.index') }}" class="ghost-btn">Cancel</a>
              <button type="submit" class="primary-btn"><i class="bi bi-person-check"></i> Create Account</button>
            </div>
          </form>
        </section>

      </main>
    </div>

    <script>
      function togglePw(fieldId, eyeId) {
        const f = document.getElementById(fieldId);
        const e = document.getElementById(eyeId);
        if (f.type === 'password') { f.type = 'text'; e.className = 'bi bi-eye-slash'; }
        else { f.type = 'password'; e.className = 'bi bi-eye'; }
      }
    </script>
    <script src="{{ asset('assets/js/dashboard.js') }}"></script>
  </body>
</html>
