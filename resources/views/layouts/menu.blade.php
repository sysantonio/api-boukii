
<li class="nav-item">
    <a href="{{ route('stations.index') }}" class="nav-link {{ Request::is('stations*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-home"></i>
        <p>@lang('models/stations.plural')</p>
    </a>
</li>
