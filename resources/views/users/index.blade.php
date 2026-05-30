<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>User Management - Country Yoghurt MD</title>
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
            <h2>User Management</h2>
            <p>Manage staff accounts and distributors.</p>
          </div>
          <div class="top-actions">
            <a href="{{ route('admin.users.create', ['role'=>'distributor']) }}" class="ghost-btn">
              <i class="bi bi-person-plus"></i> Add Distributor
            </a>
            <a href="{{ route('admin.users.create', ['role'=>'general_manager']) }}" class="primary-btn">
              <i class="bi bi-person-badge"></i> Add Staff
            </a>
          </div>
        </header>

        @if (session('success'))
          <div class="lp-success" style="margin-bottom:14px;"><i class="bi bi-check-circle"></i> {{ session('success') }}</div>
        @endif
        @if (session('error'))
          <div class="lp-error" style="margin-bottom:14px;"><i class="bi bi-exclamation-circle"></i> {{ session('error') }}</div>
        @endif

        {{-- KPI tabs --}}
        <section class="role-tabs" style="margin-bottom:16px;">
          @php $roles = ['all'=>'All','general_manager'=>'Managers','production_manager'=>'Production','store_manager'=>'Store','distributor'=>'Distributors'] @endphp
          @foreach ($roles as $key => $label)
            <a href="{{ route('admin.users.index', array_merge(request()->except('page'), ['role'=>$key])) }}"
               class="role-tab {{ $role === $key ? 'active' : '' }}">
              {{ $label }}
              <span class="role-tab-count">{{ $counts[$key] }}</span>
            </a>
          @endforeach
        </section>

        {{-- Search --}}
        <section class="card inv-filter-bar" style="margin-bottom:16px;">
          <form method="GET" action="{{ route('admin.users.index') }}" class="inv-filters">
            <input type="hidden" name="role" value="{{ $role }}" />
            <label class="search-wrap inv-search">
              <i class="bi bi-search search-icon"></i>
              <input type="search" name="search" placeholder="Search name, phone…" value="{{ $search }}" />
            </label>
            <button type="submit" class="ghost-btn">Apply</button>
            @if ($search)
              <a href="{{ route('admin.users.index', ['role'=>$role]) }}" class="ghost-btn">Clear</a>
            @endif
          </form>
          <span class="inv-count">{{ $users->total() }} user{{ $users->total() !== 1 ? 's' : '' }}</span>
        </section>

        {{-- Users table --}}
        <section class="card table-card">
          <div class="table-scroll">
            <table class="inv-table">
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Phone</th>
                  <th>Role</th>
                  <th>Company / State</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($users as $u)
                  <tr>
                    <td>
                      <span class="inv-name">{{ $u->name }}</span>
                      @if ($u->email)
                        <span class="inv-notes">{{ $u->email }}</span>
                      @endif
                    </td>
                    <td><code class="sku-code">{{ $u->phone }}</code></td>
                    <td><span class="role-badge role-{{ str_replace('_','-',$u->role) }}">{{ $u->role_label }}</span></td>
                    <td>
                      {{ $u->company_name ?: '-' }}
                      @if ($u->state) <span class="inv-notes">{{ $u->state }}{{ $u->lga ? ' - ' . $u->lga : '' }}</span> @endif
                    </td>
                    <td>
                      @if ($u->is_active)
                        <span class="status-badge badge-active">Active</span>
                      @else
                        <span class="status-badge badge-inactive">Inactive</span>
                      @endif
                    </td>
                    <td>
                      <div class="inv-actions">
                        <a href="{{ route('admin.users.edit', $u) }}" class="ghost-btn btn-sm" title="Edit">
                          <i class="bi bi-pencil"></i>
                        </a>
                        <form method="POST" action="{{ route('admin.users.toggleActive', $u) }}" style="display: inline;">
                          @csrf @method('PATCH')
                          <button type="submit" class="ghost-btn btn-sm"
                                  title="{{ $u->is_active ? 'Deactivate' : 'Activate' }}">
                            <i class="bi bi-{{ $u->is_active ? 'toggle-on' : 'toggle-off' }}"></i>
                          </button>
                        </form>
                        <form method="POST" action="{{ route('admin.users.impersonate', $u) }}" style="display: inline;">
                          @csrf
                          <button type="submit" class="ghost-btn btn-sm" title="Impersonate (Log in as user)">
                            <i class="bi bi-box-arrow-in-right"></i>
                          </button>
                        </form>
                        <form method="POST" action="{{ route('admin.users.destroy', $u) }}"
                              onsubmit="return confirm('Are you sure you want to permanently delete user &quot;{{ $u->name }}&quot;? This will also delete their related records (pricing, invoices, payments, etc.) and cannot be undone.')"
                              style="display: inline;">
                          @csrf @method('DELETE')
                          <button type="submit" class="danger-ghost btn-sm" title="Delete">
                            <i class="bi bi-trash"></i>
                          </button>
                        </form>
                      </div>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="6" class="empty-row">
                      <i class="bi bi-people" style="font-size:1.5rem;display:block;margin-bottom:8px;color:#ccc;"></i>
                      No users found in this role.
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
          @if ($users->hasPages())
            <div class="pagination-wrap">{{ $users->links() }}</div>
          @endif
        </section>

      </main>
    </div>
    <script src="{{ asset('assets/js/dashboard.js') }}"></script>
  </body>
</html>
