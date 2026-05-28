{{--
  Shared sidebar for Country Yoghurt Manufacturing & Distribution.
  Requires: $user (Illuminate\Foundation\Auth\User)
--}}
@php
  use App\Models\User;
  $_u = auth()->user();
  $unread = $_u?->unreadNotifications()->count() ?? 0;
@endphp

<button class="sidebar-close" id="sidebarClose" aria-label="Close navigation">
  <i class="bi bi-x-lg"></i>
</button>

<div class="brand-block">
  <img src="{{ asset('assets/img/logo.png') }}" alt="Country Yoghurt logo"
       style="height:48px;width:48px;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,.08);" />
  <div>
    <h1>Country Yoghurt</h1>
    <p>Mfg & Distribution</p>
  </div>
</div>

{{-- Main Menu --}}
<p class="menu-label">Main Menu</p>
<nav class="nav-links">

  {{-- Dashboard — all roles --}}
  <a href="{{ route('dashboard') }}"
     class="nav-link nav-link-anchor {{ request()->routeIs('dashboard') ? 'active' : '' }}">
    <i class="bi bi-grid-1x2 nav-icon"></i>Dashboard
  </a>

  {{-- Super Admin & General Manager: oversight pages --}}
  @if ($user->isOversight())
    <a href="{{ route('admin.inventory.index') }}"
       class="nav-link nav-link-anchor {{ request()->routeIs('admin.inventory.*') ? 'active' : '' }}">
      <i class="bi bi-layers nav-icon"></i>Inventory
    </a>

    <a href="{{ route('admin.dispatches.index') }}"
       class="nav-link nav-link-anchor {{ request()->routeIs('admin.dispatches.*') ? 'active' : '' }}">
      <i class="bi bi-truck nav-icon"></i>Dispatches
    </a>

    <a href="{{ route('admin.distributors.index') }}"
       class="nav-link nav-link-anchor {{ request()->routeIs('admin.distributors.*') ? 'active' : '' }}">
      <i class="bi bi-people nav-icon"></i>Distributors
    </a>

    <a href="{{ route('admin.payments.index') }}"
       class="nav-link nav-link-anchor {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}">
      <i class="bi bi-credit-card nav-icon"></i>Payments
    </a>
  @endif

  {{-- Super Admin only: user management, reports, pricing, products --}}
  @if ($user->isSuperAdmin())
    <a href="{{ route('admin.products.index') }}"
       class="nav-link nav-link-anchor {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
      <i class="bi bi-box-seam nav-icon"></i>Products
    </a>

    <a href="{{ route('admin.pricing.index') }}"
       class="nav-link nav-link-anchor {{ request()->routeIs('admin.pricing.*') ? 'active' : '' }}">
      <i class="bi bi-tag nav-icon"></i>Pricing & Discounts
    </a>

    <a href="{{ route('admin.users.index') }}"
       class="nav-link nav-link-anchor {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
      <i class="bi bi-person-gear nav-icon"></i>User Management
    </a>
  @endif

  {{-- Oversight: reports --}}
  @if ($user->isOversight())
    <a href="{{ route('admin.reports.index') }}"
       class="nav-link nav-link-anchor {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
      <i class="bi bi-bar-chart-line nav-icon"></i>Reports
    </a>
  @endif

  {{-- Production Manager --}}
  @if ($user->isProductionManager())
    <a href="{{ route('production.batches.index') }}"
       class="nav-link nav-link-anchor {{ request()->routeIs('production.batches.*') ? 'active' : '' }}">
      <i class="bi bi-clipboard-plus nav-icon"></i>Upload Inventory
    </a>

    <a href="{{ route('production.products.index') }}"
       class="nav-link nav-link-anchor {{ request()->routeIs('production.products.*') ? 'active' : '' }}">
      <i class="bi bi-box-seam nav-icon"></i>Products
    </a>
  @endif

  {{-- Store Manager --}}
  @if ($user->isStoreManager())
    <a href="{{ route('store.inventory.index') }}"
       class="nav-link nav-link-anchor {{ request()->routeIs('store.inventory.*') ? 'active' : '' }}">
      <i class="bi bi-layers nav-icon"></i>Inventory
    </a>

    <a href="{{ route('store.dispatches.index') }}"
       class="nav-link nav-link-anchor {{ request()->routeIs('store.dispatches.*') ? 'active' : '' }}">
      <i class="bi bi-truck nav-icon"></i>Dispatches
    </a>
  @endif

  {{-- Distributor --}}
  @if ($user->isDistributor())
    <a href="{{ route('distributor.received.index') }}"
       class="nav-link nav-link-anchor {{ request()->routeIs('distributor.received.*') ? 'active' : '' }}">
      <i class="bi bi-box-arrow-in-down nav-icon"></i>Received Products
    </a>

    <a href="{{ route('distributor.invoices.index') }}"
       class="nav-link nav-link-anchor {{ request()->routeIs('distributor.invoices.*') ? 'active' : '' }}">
      <i class="bi bi-receipt nav-icon"></i>Invoices
    </a>

    <a href="{{ route('distributor.payments.index') }}"
       class="nav-link nav-link-anchor {{ request()->routeIs('distributor.payments.*') ? 'active' : '' }}">
      <i class="bi bi-credit-card nav-icon"></i>Payments
    </a>
  @endif

  {{-- Notifications — all roles --}}
  <a href="{{ route('notifications.index') }}"
     class="nav-link nav-link-anchor {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
    <i class="bi bi-bell nav-icon"></i>Notifications
    @if ($unread > 0)
      <span class="notif-nav-badge">{{ $unread > 99 ? '99+' : $unread }}</span>
    @endif
  </a>

</nav>

{{-- Footer --}}
<div class="sidebar-footer">
  <div class="user-avatar">
    {{ strtoupper(substr($user->name, 0, 1)) }}{{ strtoupper(substr(strrchr($user->name, ' ') ?: $user->name, 1, 1)) }}
  </div>
  <div class="user-meta">
    <p>{{ $user->name }}</p>
    <small>{{ $user->role_label }}</small>
  </div>
  <form method="POST" action="{{ route('logout') }}" class="logout-form">
    @csrf
    <button type="submit" class="logout-btn" title="Sign out">
      <i class="bi bi-box-arrow-right"></i>
    </button>
  </form>
</div>
