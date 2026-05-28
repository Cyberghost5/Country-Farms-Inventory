<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Notifications - Country Yoghurt MD</title>
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
      <aside class="sidebar" id="sidebar">
        @include('partials._sidebar')
      </aside>

      <main class="main-content">
        <header class="topbar">
          <div class="title-block">
            <h2>Notifications</h2>
            <p>Stay updated with the latest system activities.</p>
          </div>
          @if ($user->unreadNotifications->count() > 0)
            <div class="top-actions">
              <form method="POST" action="{{ route('notifications.readAll') }}">
                @csrf
                <button type="submit" class="ghost-btn">
                  <i class="bi bi-check-all"></i> Mark All as Read
                </button>
              </form>
            </div>
          @endif
        </header>

        {{-- Flash messages --}}
        @if (session('success'))
          <div class="lp-success" style="margin-bottom:14px;">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
          </div>
        @endif

        <div class="card table-card" style="padding: 24px;">
          @if ($notifications->isEmpty())
            <div style="text-align:center; padding: 40px 0; color: #6c757d;">
              <i class="bi bi-bell-slash" style="font-size:3rem; display:block; margin-bottom:12px; color:#ccc;"></i>
              <p>You have no notifications yet.</p>
            </div>
          @else
            <div class="notifications-list" style="display: flex; flex-direction: column; gap: 12px;">
              @foreach ($notifications as $notification)
                @php
                  $isUnread = is_null($notification->read_at);
                  $data = $notification->data;
                  $notifType = $data['type'] ?? 'info';
                  $bg = $isUnread ? '#f5f4fd' : '#fff';
                  $border = $isUnread ? '1px solid #d4cbf5' : '1px solid #e9ecef';
                  $icon = match($notifType) {
                      'upload' => 'bi-clipboard-plus',
                      'verification' => 'bi-check-circle-fill',
                      default => 'bi-info-circle-fill',
                  };
                  $iconColor = match($notifType) {
                      'upload' => '#1d086c',
                      'verification' => '#2e7d32',
                      default => '#1565c0',
                  };
                @endphp
                <div class="notification-item" style="background: {{ $bg }}; border: {{ $border }}; border-radius: 8px; padding: 16px; display: flex; justify-content: space-between; align-items: center; gap: 16px; transition: all 0.2s;">
                  <div style="display: flex; align-items: center; gap: 16px;">
                    <div style="font-size: 1.5rem; color: {{ $iconColor }}; background: {{ $isUnread ? '#fff' : '#f8f9fa' }}; width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 1px solid #e9ecef; flex-shrink: 0;">
                      <i class="bi {{ $icon }}"></i>
                    </div>
                    <div>
                      <p style="margin: 0; font-size: 0.95rem; font-weight: {{ $isUnread ? '600' : '400' }}; color: #333; line-height: 1.4;">
                        {{ $data['message'] ?? 'System notification' }}
                      </p>
                      <small style="color: #888; font-size: 0.8rem; display: block; margin-top: 4px;">
                        {{ $notification->created_at->diffForHumans() }}
                      </small>
                    </div>
                  </div>
                  @if ($isUnread)
                    <form method="POST" action="{{ route('notifications.read', $notification->id) }}" style="flex-shrink: 0;">
                      @csrf
                      <button type="submit" class="ghost-btn btn-sm" title="Mark as Read" style="padding: 4px 8px; font-size: 0.8rem; height: auto;">
                        Mark Read
                      </button>
                    </form>
                  @endif
                </div>
              @endforeach
            </div>
            @if ($notifications->hasPages())
              <div class="pagination-wrap" style="margin-top: 20px;">
                {{ $notifications->links() }}
              </div>
            @endif
          @endif
        </div>
      </main>
    </div>

    <script src="{{ asset('assets/js/dashboard.js') }}"></script>
  </body>
</html>
