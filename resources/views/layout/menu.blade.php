<div id="ieducar-quick-search" class="ieducar-quick-search">
    <h4 class="ieducar-quick-search-title">{{ __('Busca rápida') }}</h4>
    <quick-search></quick-search>
</div>
<ul class="ieducar-sidebar-menu">
    @foreach($menu as $item)
        @if($item->hasLinkInSubmenu())
            <li>
                <a class="@if($root === $item->getKey()) {{ 'ieducar-sidebar-menu-active' }} @endif" href="{{ $item->link }}">
                    <i class="fa {{$item->icon}}"></i>
                    <span>{{ __($item->title) }}</span>
                </a>
            </li>
        @endif
    @endforeach
</ul>