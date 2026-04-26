<nav class="sidebar" id="sidebar">
    <div class="sidebar-logo"></div>
    <ul>
        <li><a href="{{ route('dashboard') }}"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></li>
        
        @canany(['view enquiries', 'view quotations'])
        <li class="has-submenu">
            <a href="#" class="submenu-toggle">
                <i class="fas fa-handshake"></i>
                <span>CRM & Sales</span>
                <i class="fas fa-chevron-down arrow"></i>
            </a>
            <ul class="submenu">
                @can('view enquiries')
                <li><a href="{{ route('enquiries.index') }}"><i class="fas fa-question-circle"></i><span>Enquiry</span></a></li>
                @endcan
                @can('view quotations')
                <li><a href="{{ route('quotations.index') }}"><i class="fas fa-file-invoice-dollar"></i><span>Quotations</span></a></li>
                @endcan
            </ul>
        </li>
        @endcanany

        @canany(['view purchase orders', 'view products', 'view suppliers'])
        <li class="has-submenu">
            <a href="#" class="submenu-toggle">
                <i class="fas fa-boxes"></i>
                <span>Operations</span>
                <i class="fas fa-chevron-down arrow"></i>
            </a>
            <ul class="submenu">
                @can('view purchase orders')
                <li><a href="{{ route('purchaseOrders.index') }}"><i class="fas fa-file-invoice"></i><span>Purchase Order</span></a></li>
                @endcan
                @can('view products')
                <li><a href="{{ route('products.index') }}"><i class="fas fa-box"></i><span>Products</span></a></li>
                @endcan
                @can('view suppliers')
                <li><a href="{{ route('suppliers.index') }}"><i class="fas fa-truck"></i><span>Supplier</span></a></li>
                @endcan
            </ul>
        </li>
        @endcanany

        @canany(['view users', 'manage roles'])
        <li class="has-submenu">
            <a href="#" class="submenu-toggle">
                <i class="fas fa-user-shield"></i>
                <span>User Management</span>
                <i class="fas fa-chevron-down arrow"></i>
            </a>
            <ul class="submenu">
                @can('view users')
                <li><a href="{{ route('users.index') }}"><i class="fas fa-users"></i><span>Users</span></a></li>
                @endcan
                @can('manage roles')
                <li><a href="{{ route('roles.index') }}"><i class="fas fa-shield-alt"></i><span>Roles & Permissions</span></a></li>
                @endcan
            </ul>
        </li>
        @endcanany

      @can('manage settings')
      <li class="has-submenu">
          <a href="#" class="submenu-toggle">
              <i class="fas fa-cogs"></i>
              <span>Master Data</span>
              <i class="fas fa-chevron-down arrow"></i>
          </a>
          <ul class="submenu">
              <li><a href="{{ route('enquiry-types.index') }}"><i class="fas fa-tags"></i><span> Enquiry Type</span></a></li>
              <li><a href="{{ route('projects.index') }}"><i class="fas fa-project-diagram"></i><span> Project</span></a></li>
              <li><a href="{{ route('companies.index') }}"><i class="fas fa-building"></i><span> Company</span></a></li>
              <li><a href="{{ route('services.index') }}"><i class="fas fa-concierge-bell"></i><span> Services</span></a></li>
              <li><a href="{{ route('service-items.index') }}"><i class="fas fa-list-ul"></i><span> Service Items</span></a></li>
              <li><a href="{{ route('terms-conditions.index') }}"><i class="fas fa-file-contract"></i><span> Terms & Conditions</span></a></li>
              <li><a href="{{ route('vendors.index') }}"><i class="fas fa-handshake"></i><span> Vendors</span></a></li>
              <li><a href="{{ route('units.index') }}"><i class="fas fa-database"></i><span> Unit</span></a></li>
              <li><a href="{{ route('sources.index') }}"><i class="fas fa-database"></i><span> Source</span></a></li>
          </ul>
      </li>
      @endcan
    </ul>
</nav>