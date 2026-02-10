<nav class="sidebar" id="sidebar">
    <div class="sidebar-logo"></div>
    <ul>
        <li><a href="{{ route('dashboard') }}"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></li>
        <li><a href="{{ route('enquiries.index') }}"><i class="fas fa-question-circle"></i><span>Enquiry</span></a></li>
        {{-- <li><a href="{{ route('purchaseOrders.index') }}"><i class="fas fa-file-invoice"></i><span>Purchase Order</span></a></li> --}}
        <li><a href="{{ route('users.index') }}"><i class="fas fa-users"></i><span>Users</span></a></li>
        {{-- <li><a href="{{ route('suppliers.index') }}"><i class="fas fa-truck"></i><span>Supplier</span></a></li> --}}
        {{-- <li><a href="{{ route('products.index') }}"><i class="fas fa-box"></i><span>Products</span></a></li> --}}
        <!-- Add other nav items as needed -->

      <!-- Settings Submenu -->
      <li class="has-submenu">
          <a href="#" class="submenu-toggle">
              <i class="fas fa-cogs"></i>
              <span>Settings</span>
              <i class="fas fa-chevron-down arrow"></i>
          </a>
          <ul class="submenu">
              <li>
                <a href="{{ route('enquiry-types.index') }}"><i class="fas fa-tags"></i><span> Enquiry Type</span></a>
              </li>
              {{-- <li>
                  <a href="{{ route('companies.index') }}"><i class="fas fa-building"></i><span> Company</span></a>
              </li>
              <li>
                <a href="{{ route('projects.index') }}"><i class="fas fa-project-diagram"></i><span> Projects</span></a>
              </li> --}}
              {{-- <li>
                  <a href="{{ route('uoms.index') }}"><i class="fas fa-database"></i><span> UOM</span></a>
              </li>
              <li>
                  <a href="{{ route('categories.index') }}"><i class="fas fa-shield-alt"></i><span> Category</span></a>
              </li> --}}
              <li>
                  <a href="{{ route('sources.index') }}"><i class="fas fa-database"></i><span> Source</span></a>
              </li>
          </ul>
      </li>
    </ul>
</nav>