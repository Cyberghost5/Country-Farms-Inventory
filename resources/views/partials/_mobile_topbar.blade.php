{{--
  Mobile top bar for Country Yoghurt MD
  Requires: $user (auth user)
--}}
@php $unread = auth()->user()?->unreadNotifications()->count() ?? 0; @endphp

@if (session()->has('impersonator_id'))
  <div class="impersonate-banner">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <span>You are currently logged in as <strong>{{ auth()->user()->name }}</strong> ({{ auth()->user()->role_label }}).</span>
    <form method="POST" action="{{ route('admin.users.stop-impersonate') }}" style="display: inline; margin-left: 10px;">
      @csrf
      <button type="submit" class="impersonate-stop-btn">
        Stop Impersonating
      </button>
    </form>
  </div>
@endif

<div class="mobile-topbar">
  <button class="hamburger" id="sidebarToggle" aria-label="Open navigation">
    <i class="bi bi-list"></i>
  </button>
  <span class="mobile-brand">Country Yoghurt MD</span>
  <div class="mobile-topbar-right">
    <a href="{{ route('notifications.index') }}" class="icon-btn mobile-icon-btn notif-bell-btn" aria-label="Notifications">
      <i class="bi bi-bell"></i>
      @if ($unread > 0)
        <span class="notif-badge">{{ $unread > 99 ? '99+' : $unread }}</span>
      @endif
    </a>
  </div>
</div>
