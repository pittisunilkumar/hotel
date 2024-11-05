<?php
use App\CentralLogics\Helpers;
$order = \App\Models\Order::Notpos()
    ->HasSubscriptionToday()
    ->selectRaw(
        'COUNT(*) as total,
                        COUNT(CASE WHEN order_status = "delivered" THEN 1 END) as delivered,
                        COUNT(CASE WHEN order_status = "canceled" THEN 1 END) as canceled,
                        COUNT(CASE WHEN order_status = "failed" THEN 1 END) as failed,
                        COUNT(CASE WHEN order_status = "refunded" THEN 1 END) as refunded,
                        COUNT(CASE WHEN order_status = "refund_requested" THEN 1 END) as refund_requested,
                        COUNT(CASE WHEN order_status IN ("confirmed", "processing","handover") THEN 1 END) as processing,
                        COUNT(CASE WHEN created_at <> schedule_at AND scheduled = 1 THEN 1 END) as scheduled',
    )
    ->first();

$order_sch = \App\Models\Order::Notpos()
    ->HasSubscriptionToday()
    ->OrderScheduledIn(30)
    ->selectRaw(
        'COUNT(CASE WHEN order_status = "pending" THEN 1 END) as pending,
                        COUNT(CASE WHEN order_status = "picked_up" THEN 1 END) as picked_up,
                        COUNT(CASE WHEN order_status IN ("accepted", "confirmed","processing","handover","picked_up") THEN 1 END) as ongoing,
                        COUNT(CASE WHEN delivery_man_id IS NULL  AND order_type = "delivery" AND order_status NOT IN ("delivered", "failed","canceled","refund_requested","refund_request_canceled","refunded") THEN 1 END) as searching_dm,
                        COUNT(CASE WHEN order_status = "accepted" THEN 1 END) as accepted',
    )
    ->first();
?>

<div id="sidebarMain" class="d-none">
    <aside
        class="js-navbar-vertical-aside navbar navbar-vertical-aside navbar-vertical navbar-vertical-fixed navbar-expand-xl navbar-bordered  ">
        <div class="navbar-vertical-container">
            <div class="navbar__brand-wrapper navbar-brand-wrapper justify-content-between">
                <!-- Logo -->
                @php($restaurant_logo = \App\Models\BusinessSetting::where(['key' => 'logo'])->first())
                <a class="navbar-brand d-block p-0" href="{{ route('admin.dashboard') }}" aria-label="Front">
                    <img class="navbar-brand-logo sidebar--logo-design"
                        src="{{ Helpers::get_full_url('business', $restaurant_logo?->value, $restaurant_logo?->storage[0]?->value ?? 'public', 'favicon') }}"
                        alt="image">
                    <img class="navbar-brand-logo-mini sidebar--logo-design-2"
                        src="{{ Helpers::get_full_url('business', $restaurant_logo?->value, $restaurant_logo?->storage[0]?->value ?? 'public', 'favicon') }}"
                        alt="image">
                </a>
                <!-- End Logo -->

                <!-- Navbar Vertical Toggle -->
                <button type="button"
                    class="js-navbar-vertical-aside-toggle-invoker navbar-vertical-aside-toggle btn btn-icon btn-xs btn-ghost-dark">
                    <i class="tio-clear tio-lg"></i>
                </button>
                <!-- End Navbar Vertical Toggle -->

                <div class="navbar-nav-wrap-content-left d-none d-xl-block">
                    <!-- Navbar Vertical Toggle -->
                    <button type="button" class="js-navbar-vertical-aside-toggle-invoker close">
                        <i class="tio-first-page navbar-vertical-aside-toggle-short-align" data-toggle="tooltip"
                            data-placement="right" title="Collapse"></i>
                        <i class="tio-last-page navbar-vertical-aside-toggle-full-align"
                            data-template='<div class="tooltip d-none" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>'
                            data-toggle="tooltip" data-placement="right" title="Expand"></i>
                    </button>
                    <!-- End Navbar Vertical Toggle -->
                </div>

            </div>

            <!-- Content -->
            <div class="navbar-vertical-content bg--title" id="navbar-vertical-content">
                <!-- Search Form -->
                <form class="sidebar--search-form" autocomplete="off">
                    <input autocomplete="false" name="hidden" type="text" class="d-none">
                    <div class="search--form-group">
                        <button type="button" class="btn"><i class="tio-search"></i></button>
                        <input type="text" id="search" class="form-control form--control"
                            placeholder="{{ translate('messages.Search_Menu...') }}">
                    </div>
                </form>
                <!-- Search Form -->
                <ul class="navbar-nav navbar-nav-lg nav-tabs">
                    <!-- Dashboards -->
                    <li class="navbar-vertical-aside-has-menu {{ Request::is('admin') ? 'active' : '' }}">
                        <a class="js-navbar-vertical-aside-menu-link nav-link" href="{{ route('admin.dashboard') }}"
                            title="{{ translate('messages.dashboard') }}">
                            <i class="tio-dashboard-vs nav-icon"></i>
                            <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                {{ translate('messages.dashboard') }}
                            </span>
                        </a>
                    </li>
                    <!-- End Dashboards -->
                    @if (Helpers::module_permission_check('pos'))
                        <!-- POS -->
                        <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/pos') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link" href="{{ route('admin.pos.index') }}"
                                title="{{ translate('messages.pos') }}">
                                <i class="tio-receipt nav-icon"></i>
                                <span
                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('messages.Point_of_Sale') }}</span>
                            </a>
                        </li>
                        <!-- End POS -->
                    @endif

                    <!-- Account -->
                    @if (Helpers::module_permission_check('order'))
                        <li class="nav-item">
                            <small class="nav-subtitle">{{ translate('messages.account_management') }}</small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>

                        <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/order*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:"
                                title="{{ translate('messages.account') }}">
                                <i class="tio-file-text-outlined nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ translate('messages.account') }}
                                </span>
                            </a>
                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                style="display: {{ Request::is('admin/order*') && (Request::is('admin/order/subscription*') == false && Request::is('admin/order-cancel-reasons') == false) ? 'block' : 'none' }}">
                                <li
                                    class="nav-item {{ Request::is('admin/order/list/all') ? 'active' : '' }} @yield('all_order')">
                                    <a class="nav-link" href="{{ route('admin.order.list', ['all']) }}"
                                        title="{{ translate('messages.financial_year') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            {{ translate('messages.financial_year') }}
                                            <span class="badge badge-soft-info badge-pill ml-1">
                                                {{-- {{ \App\Models\Order::Notpos()->HasSubscriptionToday()->count() }} ==
                                            --}}
                                                {{ $order->total }}
                                            </span>
                                        </span>
                                    </a>
                                </li>
                                <li
                                    class="nav-item {{ Request::is('admin/order/list/scheduled') ? 'active' : '' }} @yield('scheduled')">
                                    <a class="nav-link" href="{{ route('admin.order.list', ['scheduled']) }}"
                                        title="{{ translate('messages.financial_year_ending') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            {{ translate('messages.financial_year_ending') }}
                                            <span class="badge badge-soft-info badge-pill ml-1">
                                                {{-- {{ \App\Models\Order::Scheduled()->HasSubscriptionToday()->count() }}
                                            == --}}
                                                {{ $order->scheduled }}
                                            </span>
                                        </span>
                                    </a>
                                </li>
                                <li
                                    class="nav-item {{ Request::is('admin/order/list/pending') ? 'active' : '' }} @yield('pending')">
                                    <a class="nav-link " href="{{ route('admin.order.list', ['pending']) }}"
                                        title="{{ translate('messages.chart_of_account') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            {{ translate('messages.chart_of_account') }}
                                            <span class="badge badge-soft-info badge-pill ml-1">
                                                {{-- {{
                                            \App\Models\Order::Pending()->HasSubscriptionToday()->OrderScheduledIn(30)->count()
                                            }} == --}}
                                                {{ $order_sch->pending }}
                                            </span>
                                        </span>
                                    </a>
                                </li>

                                <li
                                    class="nav-item {{ Request::is('admin/order/list/accepted') ? 'active' : '' }} @yield('accepted')">
                                    <a class="nav-link " href="{{ route('admin.order.list', ['accepted']) }}"
                                        title="{{ translate('messages.opening_balance') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            {{ translate('messages.opening_balance') }}
                                            <span class="badge badge-soft-success badge-pill ml-1">
                                                {{-- {{
                                            \App\Models\Order::AccepteByDeliveryman()->HasSubscriptionToday()->OrderScheduledIn(30)->count()
                                            }} == --}}

                                                {{ $order_sch->accepted }}

                                            </span>
                                        </span>
                                    </a>
                                </li>
                                <li
                                    class="nav-item {{ Request::is('admin/order/list/processing') ? 'active' : '' }} @yield('processing')">
                                    <a class="nav-link " href="{{ route('admin.order.list', ['processing']) }}"
                                        title="{{ translate('messages.debit_voucher') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            {{ translate('messages.debit_voucher') }}
                                            <span class="badge badge-soft-warning badge-pill ml-1">
                                                {{-- {{ \App\Models\Order::whereIn('order_status',
                                            ['confirmed','processing','handover'])->HasSubscriptionToday()->count() }}==
                                            --}}
                                                {{ $order->processing }}
                                            </span>
                                        </span>
                                    </a>
                                </li>
                                <li
                                    class="nav-item {{ Request::is('admin/order/list/food_on_the_way') ? 'active' : '' }} @yield('picked_up')">
                                    <a class="nav-link text-capitalize"
                                        href="{{ route('admin.order.list', ['food_on_the_way']) }}"
                                        title="{{ translate('messages.credit_voucher') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            {{ translate('messages.credit_voucher') }}
                                            <span class="badge badge-soft-warning badge-pill ml-1">
                                                {{-- {{
                                            \App\Models\Order::FoodOnTheWay()->HasSubscriptionToday()->OrderScheduledIn(30)->count()
                                            }}== --}}
                                                {{ $order_sch->picked_up }}

                                            </span>
                                        </span>
                                    </a>
                                </li>
                                <li
                                    class="nav-item {{ Request::is('admin/order/list/delivered') ? 'active' : '' }} @yield('delivered')">
                                    <a class="nav-link " href="{{ route('admin.order.list', ['delivered']) }}"
                                        title="{{ translate('messages.contra_voucher') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            {{ translate('messages.contra_voucher') }}
                                            <span class="badge badge-soft-success badge-pill ml-1">
                                                {{-- {{
                                            \App\Models\Order::Delivered()->HasSubscriptionToday()->Notpos()->count()
                                            }}== --}}
                                                {{ $order->delivered }}
                                            </span>
                                        </span>
                                    </a>
                                </li>
                                <li
                                    class="nav-item {{ Request::is('admin/order/list/canceled') ? 'active' : '' }} @yield('canceled')">
                                    <a class="nav-link " href="{{ route('admin.order.list', ['canceled']) }}"
                                        title="{{ translate('messages.journal_voucher') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            {{ translate('messages.journal_voucher') }}
                                            <span class="badge badge-soft-danger badge-pill ml-1">
                                                {{-- {{ \App\Models\Order::Canceled()->HasSubscriptionToday()->count() }}==
                                            --}}
                                                {{ $order->canceled }}
                                            </span>
                                        </span>
                                    </a>
                                </li>
                                <li
                                    class="nav-item {{ Request::is('admin/order/list/failed') ? 'active' : '' }} @yield('failed')">
                                    <a class="nav-link " href="{{ route('admin.order.list', ['failed']) }}"
                                        title="{{ translate('messages.voucher_approval') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container text-capitalize">
                                            {{ translate('messages.voucher_approval') }}
                                            <span class="badge badge-soft-danger badge-pill ml-1">
                                                {{-- {{ \App\Models\Order::failed()->HasSubscriptionToday()->count() }}==
                                            --}}
                                                {{ $order->failed }}
                                            </span>
                                        </span>
                                    </a>
                                </li>
                                
                            </ul>
                        </li>


                        <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/order*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:"
                                title="{{ translate('messages.account_report') }}">
                                <i class="tio-file-text-outlined nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ translate('messages.account_report') }}
                                </span>
                            </a>
                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                style="display: {{ Request::is('admin/order*') && (Request::is('admin/order/subscription*') == false && Request::is('admin/order-cancel-reasons') == false) ? 'block' : 'none' }}">
                                <li
                                    class="nav-item {{ Request::is('admin/order/list/all') ? 'active' : '' }} @yield('all_order')">
                                    <a class="nav-link" href="{{ route('admin.order.list', ['all']) }}"
                                        title="{{ translate('messages.voucher_report') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            {{ translate('messages.voucher_report') }}
                                            <span class="badge badge-soft-info badge-pill ml-1">
                                                {{-- {{ \App\Models\Order::Notpos()->HasSubscriptionToday()->count() }} ==
                                            --}}
                                                {{ $order->total }}
                                            </span>
                                        </span>
                                    </a>
                                </li>
                                <li
                                    class="nav-item {{ Request::is('admin/order/list/scheduled') ? 'active' : '' }} @yield('scheduled')">
                                    <a class="nav-link" href="{{ route('admin.order.list', ['scheduled']) }}"
                                        title="{{ translate('messages.Cash_book') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            {{ translate('messages.Cash_book') }}
                                            <span class="badge badge-soft-info badge-pill ml-1">
                                                {{-- {{ \App\Models\Order::Scheduled()->HasSubscriptionToday()->count() }}
                                            == --}}
                                                {{ $order->scheduled }}
                                            </span>
                                        </span>
                                    </a>
                                </li>
                                <li
                                    class="nav-item {{ Request::is('admin/order/list/pending') ? 'active' : '' }} @yield('pending')">
                                    <a class="nav-link " href="{{ route('admin.order.list', ['pending']) }}"
                                        title="{{ translate('messages.bank_book') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            {{ translate('messages.bank_book') }}
                                            <span class="badge badge-soft-info badge-pill ml-1">
                                                {{-- {{
                                            \App\Models\Order::Pending()->HasSubscriptionToday()->OrderScheduledIn(30)->count()
                                            }} == --}}
                                                {{ $order_sch->pending }}
                                            </span>
                                        </span>
                                    </a>
                                </li>

                                <li
                                    class="nav-item {{ Request::is('admin/order/list/accepted') ? 'active' : '' }} @yield('accepted')">
                                    <a class="nav-link " href="{{ route('admin.order.list', ['accepted']) }}"
                                        title="{{ translate('messages.general_ledger') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            {{ translate('messages.general_ledger') }}
                                            <span class="badge badge-soft-success badge-pill ml-1">
                                                {{-- {{
                                            \App\Models\Order::AccepteByDeliveryman()->HasSubscriptionToday()->OrderScheduledIn(30)->count()
                                            }} == --}}

                                                {{ $order_sch->accepted }}

                                            </span>
                                        </span>
                                    </a>
                                </li>
                                <li
                                    class="nav-item {{ Request::is('admin/order/list/processing') ? 'active' : '' }} @yield('processing')">
                                    <a class="nav-link " href="{{ route('admin.order.list', ['processing']) }}"
                                        title="{{ translate('messages.trial_balance') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            {{ translate('messages.trial_balance') }}
                                            <span class="badge badge-soft-warning badge-pill ml-1">
                                                {{-- {{ \App\Models\Order::whereIn('order_status',
                                            ['confirmed','processing','handover'])->HasSubscriptionToday()->count() }}==
                                            --}}
                                                {{ $order->processing }}
                                            </span>
                                        </span>
                                    </a>
                                </li>
                                <li
                                    class="nav-item {{ Request::is('admin/order/list/food_on_the_way') ? 'active' : '' }} @yield('picked_up')">
                                    <a class="nav-link text-capitalize"
                                        href="{{ route('admin.order.list', ['food_on_the_way']) }}"
                                        title="{{ translate('messages.profit_loss') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            {{ translate('messages.profit_loss') }}
                                            <span class="badge badge-soft-warning badge-pill ml-1">
                                                {{-- {{
                                            \App\Models\Order::FoodOnTheWay()->HasSubscriptionToday()->OrderScheduledIn(30)->count()
                                            }}== --}}
                                                {{ $order_sch->picked_up }}

                                            </span>
                                        </span>
                                    </a>
                                </li>
                                <li
                                    class="nav-item {{ Request::is('admin/order/list/delivered') ? 'active' : '' }} @yield('delivered')">
                                    <a class="nav-link " href="{{ route('admin.order.list', ['delivered']) }}"
                                        title="{{ translate('messages.coa_print') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            {{ translate('messages.coa_print') }}
                                            <span class="badge badge-soft-success badge-pill ml-1">
                                                {{-- {{
                                            \App\Models\Order::Delivered()->HasSubscriptionToday()->Notpos()->count()
                                            }}== --}}
                                                {{ $order->delivered }}
                                            </span>
                                        </span>
                                    </a>
                                </li>
                                <li
                                    class="nav-item {{ Request::is('admin/order/list/canceled') ? 'active' : '' }} @yield('canceled')">
                                    <a class="nav-link " href="{{ route('admin.order.list', ['canceled']) }}"
                                        title="{{ translate('messages.balance_sheet') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            {{ translate('messages.balance_sheet') }}
                                            <span class="badge badge-soft-danger badge-pill ml-1">
                                                {{-- {{ \App\Models\Order::Canceled()->HasSubscriptionToday()->count() }}==
                                            --}}
                                                {{ $order->canceled }}
                                            </span>
                                        </span>
                                    </a>
                                </li>

                                
                                
                            </ul>
                        </li>

                   
                    @endif
                    <!-- End Orders -->

                    <!-- Custommer -->
                    @if (Helpers::module_permission_check('customerList') || Helpers::module_permission_check('customer_wallet'))
                        <li class="nav-item">
                            <small class="nav-subtitle"
                                title="{{ translate('messages.customer_section') }}">{{ translate('messages.customer_management') }}</small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>
                    @endif


                    @if (Helpers::module_permission_check('customerList'))
                        <li
                            class="navbar-vertical-aside-has-menu {{ !Request::is('admin/customer/wallet/report*') && Request::is('admin/customer/wallet*') ? 'active' : '' }}">

                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:"
                                title="{{ translate('messages.customers') }}">
                                <i class="tio-poi-user nav-icon"></i>
                                <span
                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate  text-capitalize">
                                    {{ translate('messages.customers') }}
                                </span>
                            </a>

                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                style="display: {{ !Request::is('admin/customer/wallet/report*') && Request::is('admin/customer/wallet*') ? 'block' : 'none' }}">
                                <li
                                    class="nav-item {{ Request::is('admin/customer/wallet/add-fund') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.customer.wallet.add-fund') }}"
                                        title="{{ translate('messages.Customer_List') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span
                                            class="text-truncate text-capitalize">{{ translate('messages.Customer_List') }}</span>
                                    </a>
                                </li>
                                <li
                                    class="nav-item {{ Request::is('admin/customer/wallet/bonus*') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.customer.wallet.bonus.add-new') }}"
                                        title="{{ translate('messages.guest_list') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span
                                            class="text-truncate text-capitalize">{{ translate('messages.guest_list') }}</span>
                                    </a>
                                </li>

                                <li
                                    class="nav-item {{ Request::is('admin/customer/wallet/bonus*') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.customer.wallet.bonus.add-new') }}"
                                        title="{{ translate('messages.wake_up_call_list') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span
                                            class="text-truncate text-capitalize">{{ translate('messages.wake_up_call_list') }}</span>
                                    </a>
                                </li>

                            </ul>
                        </li>
                    @endif

                    @if (Helpers::module_permission_check('customer_wallet'))
                        <li
                            class="navbar-vertical-aside-has-menu {{ !Request::is('admin/customer/wallet/report*') && Request::is('admin/customer/wallet*') ? 'active' : '' }}">

                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:"
                                title="{{ translate('messages.Customer_Wallet') }}">
                                <i class="tio-wallet nav-icon"></i>
                                <span
                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate  text-capitalize">
                                    {{ translate('messages.wallet') }}
                                </span>
                            </a>

                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                style="display: {{ !Request::is('admin/customer/wallet/report*') && Request::is('admin/customer/wallet*') ? 'block' : 'none' }}">
                                <li
                                    class="nav-item {{ Request::is('admin/customer/wallet/add-fund') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.customer.wallet.add-fund') }}"
                                        title="{{ translate('messages.add_fund') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span
                                            class="text-truncate text-capitalize">{{ translate('messages.add_fund') }}</span>
                                    </a>
                                </li>
                                <li
                                    class="nav-item {{ Request::is('admin/customer/wallet/bonus*') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.customer.wallet.bonus.add-new') }}"
                                        title="{{ translate('messages.bonus') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span
                                            class="text-truncate text-capitalize">{{ translate('messages.bonus') }}</span>
                                    </a>
                                </li>

                            </ul>
                        </li>
                    @endif


                    @if (Helpers::module_permission_check('customerList'))
                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/customer/loyalty-point*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link  nav-link-toggle" href="javascript:"
                                title="{{ translate('messages.loyalty_point') }}">
                                <i class="tio-medal nav-icon"></i>
                                <span
                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate  text-capitalize">
                                    {{ translate('messages.loyalty_point') }}
                                </span>
                            </a>

                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                style="display: {{ Request::is('admin/customer/loyalty-point*') ? 'block' : 'none' }}">
                                <li
                                    class="nav-item {{ Request::is('admin/customer/loyalty-point/report*') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.customer.loyalty-point.report') }}"
                                        title="{{ translate('messages.report') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span
                                            class="text-truncate text-capitalize">{{ translate('messages.report') }}</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/customer/subscribed') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                href="{{ route('admin.customer.subscribed') }}"
                                title="{{ translate('messages.Subscribed_Emails') }}">
                                <i class="tio-email-outlined nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ translate('messages.subscribed_mail_list') }}
                                </span>
                            </a>
                        </li>
                        </li>
                    @endif
                    <!-- Customers -->


                    <!-- Employee -->
                    @if (Helpers::module_permission_check('custom_role') || Helpers::module_permission_check('employee'))
                        <!-- Employee-->
                        <li class="nav-item">
                            <small class="nav-subtitle"
                                title="{{ translate('messages.employee_handle') }}">{{ translate('messages.Employee_Management') }}</small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>
                    @endif

                    @if (Helpers::module_permission_check('custom_role'))
                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/custom-role*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                href="{{ route('admin.custom-role.create') }}"
                                title="{{ translate('messages.employee_Role') }}">
                                <i class="tio-incognito nav-icon"></i>
                                <span
                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('messages.employee_Role') }}</span>
                            </a>
                        </li>
                    @endif

                    @if (Helpers::module_permission_check('employee'))
                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/employee*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:"
                                title="{{ translate('Employees') }}">
                                <i class="tio-user nav-icon"></i>
                                <span
                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('messages.employees') }}</span>
                            </a>
                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                style="display: {{ Request::is('admin/employee*') ? 'block' : 'none' }}">
                                <li class="nav-item {{ Request::is('admin/employee/add-new') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.employee.add-new') }}"
                                        title="{{ translate('messages.add_new_Employee') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span
                                            class="text-truncate">{{ translate('messages.Add_New_Employee') }}</span>
                                    </a>
                                </li>
                                <li
                                    class="nav-item {{ Request::is('admin/employee/list') || Request::is('admin/employee/update/*') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.employee.list') }}"
                                        title="{{ translate('messages.Employee_list') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{ translate('messages.Employee_List') }}</span>
                                    </a>
                                </li>

                            </ul>
                        </li>
                    @endif
                    <!-- End Employee -->



                    <!-- Help & Support -->
                    @if (Helpers::module_permission_check('chat') || Helpers::module_permission_check('contact_message'))
                        <li class="nav-item">
                            <small class="nav-subtitle">{{ translate('messages.Help_&_Support') }}</small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>
                    @endif
                    @if (Helpers::module_permission_check('chat'))
                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/message/list') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                href="{{ route('admin.message.list', ['tab' => 'customer']) }}"
                                title="{{ translate('messages.Chattings') }}">
                                <i class="tio-chat nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ translate('messages.Chattings') }}
                                </span>
                            </a>
                        </li>
                    @endif
                    @if (Helpers::module_permission_check('contact_message'))
                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/contact/*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                href="{{ route('admin.contact.list') }}"
                                title="{{ translate('messages.Contact_messages') }}">
                                <i class="tio-messages nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ translate('messages.Contact_messages') }}
                                </span>
                            </a>
                        </li>
                    @endif
                    <!-- End Help & Support -->






                <li class="nav-item pt-100px">

                </li>
                </ul>
            </div>
            <!-- End Content -->
        </div>
    </aside>
</div>

<div id="sidebarCompact" class="d-none">

</div>


@push('script_2')
    <script>
        "use strict";
        $(window).on('load', function() {
            if ($(".navbar-vertical-content li.active").length) {
                $('.navbar-vertical-content').animate({
                    scrollTop: $(".navbar-vertical-content li.active").offset().top - 150
                }, 300);
            }
        });

        var $navItems = $('#navbar-vertical-content > ul > li');
        $('#search').keyup(function() {
            var val = $.trim($(this).val()).replace(/ +/g, ' ').toLowerCase();
            $navItems.show().filter(function() {
                var $listItem = $(this);
                var text = $listItem.text().replace(/\s+/g, ' ').toLowerCase();
                var $list = $listItem.closest('li');

                return !~text.indexOf(val) && !$list.text().toLowerCase().includes(val);
            }).hide();
        });
    </script>
@endpush
