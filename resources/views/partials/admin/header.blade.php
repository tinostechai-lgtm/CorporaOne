@php
$users = \Auth::user();
$defaultAvatar = asset('assets/img/user-avatar.png');

// Get avatar URL - direct approach without Utility::get_file()
$avatarUrl = $defaultAvatar;

// Get user initials for fallback
$userInitial = strtoupper(substr($users->name ?? 'U', 0, 1));

if (!empty($users->avatar)) {
    // Check multiple possible paths
    $possiblePaths = [
        'uploads/avatar/' . $users->avatar,
        'storage/uploads/avatar/' . $users->avatar,
        'storage/avatar/' . $users->avatar,
        'avatar/' . $users->avatar,
    ];
    
    foreach ($possiblePaths as $path) {
        $fullPath = public_path($path);
        if (file_exists($fullPath)) {
            $avatarUrl = asset($path) . '?v=' . time();
            break;
        }
    }
    
    // If still not found, try with Utility::get_file as fallback
    if ($avatarUrl == $defaultAvatar) {
        try {
            $utilityUrl = \App\Models\Utility::get_file('uploads/avatar/' . $users->avatar);
            if ($utilityUrl && filter_var($utilityUrl, FILTER_VALIDATE_URL)) {
                $avatarUrl = $utilityUrl . '?v=' . time();
            }
        } catch (\Exception $e) {
            // Keep default avatar
        }
    }
}

// Check employee avatar if user has employee relationship
if ($avatarUrl == $defaultAvatar && !empty($users->employee) && !empty($users->employee->avatar)) {
    $empAvatar = $users->employee->avatar;
    $possiblePaths = [
        'uploads/avatar/' . $empAvatar,
        'storage/uploads/avatar/' . $empAvatar,
        'storage/avatar/' . $empAvatar,
        'avatar/' . $empAvatar,
    ];
    
    foreach ($possiblePaths as $path) {
        $fullPath = public_path($path);
        if (file_exists($fullPath)) {
            $avatarUrl = asset($path) . '?v=' . time();
            break;
        }
    }
    
    if ($avatarUrl == $defaultAvatar) {
        try {
            $utilityUrl = \App\Models\Utility::get_file('uploads/avatar/' . $empAvatar);
            if ($utilityUrl && filter_var($utilityUrl, FILTER_VALIDATE_URL)) {
                $avatarUrl = $utilityUrl . '?v=' . time();
            }
        } catch (\Exception $e) {
            // Keep default avatar
        }
    }
}

$languages = \App\Models\Utility::languages();
$lang = $users->lang ?? 'en';
$LangName = cache()->remember('full_language_data_' . $lang, now()->addHours(24), function () use ($lang) {
    return \App\Models\Language::languageData($lang);
});

$setting = \App\Models\Utility::settings();
$unseenCounter = App\Models\ChMessage::where('to_id', Auth::user()->id)->where('seen', 0)->count();
@endphp

<header class="dash-header {{ isset($setting['cust_theme_bg']) && $setting['cust_theme_bg'] == 'on' ? 'transprent-bg' : '' }}">
    <div class="header-wrapper">
        <div class="me-auto dash-mob-drp">
            <ul class="list-unstyled">
                <li class="dash-h-item mob-hamburger">
                    <a href="#!" class="dash-head-link" id="mobile-collapse">
                        <div class="hamburger hamburger--arrowturn">
                            <div class="hamburger-box">
                                <div class="hamburger-inner"></div>
                            </div>
                        </div>
                    </a>
                </li>

                <li class="dropdown dash-h-item drp-company">
                    <a class="dash-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                        <span class="theme-avtar position-relative">
                            @if($avatarUrl && $avatarUrl != $defaultAvatar)
                                <img src="{{ $avatarUrl }}" 
                                     class="img-fluid rounded-circle" 
                                     alt="{{ $users->name }}"
                                     style="width: 40px; height: 40px; object-fit: cover;"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="avatar-initials d-none"
                                     style="width: 40px; height: 40px; border-radius: 50%; display: none; align-items: center; justify-content: center; font-size: 16px; font-weight: 600; color: #667eea; background: #f0f2ff; border: 2px solid #667eea;">
                                    {{ $userInitial }}
                                </div>
                            @else
                                <div class="avatar-initials"
                                     style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 16px; font-weight: 600; color: #667eea; background: #f0f2ff; border: 2px solid #667eea;">
                                    {{ $userInitial }}
                                </div>
                            @endif
                        </span>
                        <span class="hide-mob ms-2">{{ __('Hi, ') }}{{ $users->name }}!</span>
                        <i class="ti ti-chevron-down drp-arrow nocolor hide-mob"></i>
                    </a>
                    <div class="dropdown-menu dash-h-dropdown">
                        <a href="{{ route('profile') }}" class="dropdown-item">
                            <i class="ti ti-user text-dark"></i><span>{{ __('Profile') }}</span>
                        </a>
                        <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('frm-logout').submit();" class="dropdown-item">
                            <i class="ti ti-power text-dark"></i><span>{{ __('Logout') }}</span>
                        </a>
                        <form id="frm-logout" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </div>
                </li>
            </ul>
        </div>
        
        <div class="ms-auto">
            <ul class="list-unstyled">
                @if($users->type == 'company')
                    @impersonating($guard = null)
                    <li class="dropdown dash-h-item drp-company">
                        <a class="btn btn-danger btn-sm me-3" href="{{ route('exit.company') }}"><i class="ti ti-ban"></i>
                            {{ __('Exit Company Login') }}
                        </a>
                    </li>
                    @endImpersonating
                @endif

                @if(!in_array($users->type, ['client', 'super admin']))
                <li class="dropdown dash-h-item drp-notification">
                    <a class="dash-head-link arrow-none me-0" href="{{ url('chatify') }}" aria-haspopup="false" aria-expanded="false">
                        <i class="ti ti-brand-hipchat"></i>
                        <span class="bg-danger dash-h-badge message-toggle-msg message-counter custom_messanger_counter beep">
                            {{ $unseenCounter }}<span class="sr-only"></span>
                        </span>
                    </a>
                </li>
                @endif

                <li class="dropdown dash-h-item drp-language">
                    <a class="dash-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                        <i class="ti ti-world nocolor"></i>
                        <span class="drp-text hide-mob">{{ ucfirst($LangName->full_name) }}</span>
                        <i class="ti ti-chevron-down drp-arrow nocolor"></i>
                    </a>
                    <div class="dropdown-menu dash-h-dropdown dropdown-menu-end">
                        @foreach ($languages as $code => $language)
                            <a href="{{ route('change.language', $code) }}" class="dropdown-item {{ $lang == $code ? 'text-primary' : '' }}">
                                <span>{{ ucFirst($language) }}</span>
                            </a>
                        @endforeach

                        @if($users->type == 'super admin')
                            <a data-url="{{ route('create.language') }}" class="dropdown-item text-primary" data-ajax-popup="true" data-title="{{ __('Create New Language') }}">
                                {{ __('Create Language') }}
                            </a>
                            <a class="dropdown-item text-primary" href="{{ route('manage.language', [$lang ?: 'english']) }}">
                                {{ __('Manage Language') }}
                            </a>
                        @endif
                    </div>
                </li>
            </ul>
        </div>
    </div>
</header>

@push('css')
<style>
    .theme-avtar {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        overflow: hidden;
    }
    .theme-avtar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .theme-avtar .avatar-initials {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        font-weight: 600;
        color: #667eea;
        background: #f0f2ff;
        border: 2px solid #667eea;
        text-transform: uppercase;
        transition: all 0.3s ease;
    }
    .theme-avtar .avatar-initials:hover {
        transform: scale(1.05);
        box-shadow: 0 0 15px rgba(102, 126, 234, 0.3);
    }
    .theme-avtar img:hover {
        transform: scale(1.05);
        transition: all 0.3s ease;
    }
    .dash-head-link {
        display: flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        padding: 5px 10px;
        border-radius: 8px;
        transition: all 0.3s ease;
    }
    .dash-head-link:hover {
        background: rgba(0,0,0,0.05);
    }
    .dash-head-link .hide-mob {
        font-weight: 500;
        color: #333;
    }
    .dash-head-link .drp-arrow {
        font-size: 12px;
        color: #6c757d;
    }
</style>
@endpush