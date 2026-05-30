<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Edit User - Country Yoghurt MD</title>
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
            <h2>Edit - {{ $user->name }}</h2>
            <p>{{ $user->role_label }}</p>
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
          <form method="POST" action="{{ route('admin.users.update', $user) }}">
            @csrf @method('PUT')

            <div class="form-row-2">
              <div class="form-group">
                <label>Full Name *</label>
                <input class="form-input" type="text" name="name" value="{{ old('name', $user->name) }}" required />
              </div>
              <div class="form-group">
                <label>Role</label>
                <input class="form-input" type="text" value="{{ $user->role_label }}" disabled />
                {{-- Role cannot be changed after creation --}}
              </div>
            </div>

            <div class="form-row-2">
              <div class="form-group">
                <label>Phone Number *</label>
                <input class="form-input" type="text" name="phone"
                       value="{{ old('phone', $user->phone) }}" required />
              </div>
              <div class="form-group">
                <label>Email</label>
                <input class="form-input" type="email" name="email"
                       value="{{ old('email', $user->email) }}" placeholder="Optional" />
              </div>
            </div>

            <div class="form-row-2">
              <div class="form-group">
                <label>Company Name</label>
                <input class="form-input" type="text" name="company_name"
                       value="{{ old('company_name', $user->company_name) }}" />
              </div>
              <div class="form-group">
                <label>State</label>
                <select class="form-input" name="state" id="stateSelect">
                  <option value="">-- Select State --</option>
                </select>
              </div>
            </div>

            <div class="form-row-2">
              <div class="form-group">
                <label>LGA (Local Government Area)</label>
                <select class="form-input" name="lga" id="lgaSelect">
                  <option value="">-- Select LGA --</option>
                </select>
              </div>
              <div class="form-group">
                {{-- Spacer --}}
              </div>
            </div>

            <div class="form-group">
              <label>Address</label>
              <textarea class="form-input" name="address" rows="2">{{ old('address', $user->address) }}</textarea>
            </div>

            <div class="form-group">
              <label>Account Status</label>
              <select class="form-input" name="is_active">
                <option value="1" {{ old('is_active', $user->is_active) ? 'selected' : '' }}>Active</option>
                <option value="0" {{ !old('is_active', $user->is_active) ? 'selected' : '' }}>Inactive</option>
              </select>
            </div>

            <hr style="border:none;border-top:1px solid #f0ebe0;margin:20px 0;" />
            <p style="font-size:.85rem;color:#888;margin-bottom:12px;">Leave password fields blank to keep existing password.</p>

            <div class="form-row-2">
              <div class="form-group">
                <label>New Password</label>
                <div class="pw-wrap">
                  <input class="form-input" type="password" name="password" id="pwField" minlength="8" />
                  <button type="button" class="pw-toggle" onclick="togglePw('pwField','pwEye')">
                    <i class="bi bi-eye" id="pwEye"></i>
                  </button>
                </div>
              </div>
              <div class="form-group">
                <label>Confirm New Password</label>
                <div class="pw-wrap">
                  <input class="form-input" type="password" name="password_confirmation" id="pwConfirm" minlength="8" />
                  <button type="button" class="pw-toggle" onclick="togglePw('pwConfirm','pwConfirmEye')">
                    <i class="bi bi-eye" id="pwConfirmEye"></i>
                  </button>
                </div>
              </div>
            </div>

            <div style="margin-top:24px;display:flex;gap:12px;justify-content:flex-end;">
              <a href="{{ route('admin.users.index') }}" class="ghost-btn">Cancel</a>
              <button type="submit" class="primary-btn"><i class="bi bi-check-lg"></i> Save Changes</button>
            </div>
          </form>
        </section>

      </main>
    </div>

    <script src="{{ asset('assets/js/states-lgas.js') }}"></script>
    <script>
      function togglePw(fieldId, eyeId) {
        const f = document.getElementById(fieldId);
        const e = document.getElementById(eyeId);
        if (f.type === 'password') { f.type = 'text'; e.className = 'bi bi-eye-slash'; }
        else { f.type = 'password'; e.className = 'bi bi-eye'; }
      }

      document.addEventListener('DOMContentLoaded', function () {
        const stateSelect = document.getElementById('stateSelect');
        const lgaSelect = document.getElementById('lgaSelect');
        const oldState = "{{ old('state', $user->state) }}";
        const oldLga = "{{ old('lga', $user->lga) }}";

        if (window.statesAndLgas) {
          // Populate states
          for (const state in window.statesAndLgas) {
            if (window.statesAndLgas.hasOwnProperty(state)) {
              if (state === 'FCT') continue; // Skip redundant alias key in list
              const opt = document.createElement('option');
              opt.value = state;
              opt.textContent = state;
              if (state === oldState) {
                opt.selected = true;
              }
              stateSelect.appendChild(opt);
            }
          }

          function populateLgas(selectedState, selectedLga = '') {
            lgaSelect.innerHTML = '<option value="">-- Select LGA --</option>';
            if (!selectedState || !window.statesAndLgas[selectedState]) return;

            window.statesAndLgas[selectedState].forEach(function (lga) {
              const opt = document.createElement('option');
              opt.value = lga;
              opt.textContent = lga;
              if (lga === selectedLga) {
                opt.selected = true;
              }
              lgaSelect.appendChild(opt);
            });
          }

          stateSelect.addEventListener('change', function () {
            populateLgas(this.value);
          });

          if (oldState) {
            populateLgas(oldState, oldLga);
          }
        }
      });
    </script>
    <script src="{{ asset('assets/js/dashboard.js') }}"></script>
  </body>
</html>
