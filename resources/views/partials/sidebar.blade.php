<!--begin::Aside menu-->
<div class="aside-menu flex-column-fluid">
    <!--begin::Aside Menu-->
    <div class="hover-scroll-overlay-y my-5 my-lg-5" id="kt_aside_menu_wrapper" data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-height="auto" data-kt-scroll-dependencies="#kt_aside_logo, #kt_aside_footer" data-kt-scroll-wrappers="#kt_aside_menu" data-kt-scroll-offset="0">
        <!--begin::Menu-->
        <div class="menu menu-column menu-title-gray-800 menu-state-title-primary menu-state-icon-primary menu-state-bullet-primary menu-arrow-gray-500" id="#kt_aside_menu" data-kt-menu="true">
            <div class="menu-item">
                <div class="menu-content pb-2">
                    <span class="menu-section text-muted text-uppercase fs-8 ls-1">Dashboard</span>
                </div>
            </div>
            <div class="menu-item">
                @foreach (getModules(request()->user()->type) as $key => $value)
                    @php
                        $have_child = count($value['child']);
                    @endphp
                    @if(empty($have_child))
                        <div class="menu-item">
                            <a class="menu-link {{ is_active_module($value['all_routes']) }}" href="{{ $value['route'] ?? 'javascript:;' }}">
										<span class="menu-icon">
											<i class="{!! $value['icon'] !!}"></i>
										</span>
                                <span class="menu-title">{{ $value['name'] }}</span>
                            </a>
                        </div>
                    @else
                        <div data-kt-menu-trigger="click" class="menu-item {{ is_active_module($value['all_routes'],true) }} menu-accordion ">
									<span class="menu-link">
										<span class="menu-icon">
											<i class="{!! $value['icon'] !!}"></i>
										</span>
										<span class="menu-title">{{ $value['name'] }}</span>
										<span class="menu-arrow"></span>
									</span>
                            <div class="menu-sub menu-sub-accordion menu-active-bg">
                                @foreach ($value['child'] as $val)
                                    <div class="menu-item">
                                        <a class="menu-link {{ is_active_module($val['all_routes']) }}" href="{{ $val['route'] }}">
												<span class="menu-bullet">
													<span class="bullet bullet-dot"></span>
												</span>
                                            <span class="menu-title">{{ $val['name'] }}</span>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
        <!--end::Menu-->
    </div>
    <!--end::Aside Menu-->
</div>
<!--end::Aside menu-->
