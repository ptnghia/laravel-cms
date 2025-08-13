<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'Admin Dashboard') | {{ config('app.name', 'Laravel CMS') }}</title>
    
    <!-- Favicon -->
    <link rel="icon" href="{{ asset('admin/images/favicon.ico') }}" type="image/x-icon">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body 
    x-data="{ 
        page: '{{ $page ?? 'dashboard' }}', 
        loaded: true, 
        darkMode: false, 
        stickyMenu: false, 
        sidebarToggle: false, 
        scrollTop: false 
    }"
    x-init="
        darkMode = JSON.parse(localStorage.getItem('darkMode'));
        $watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)))
    "
    :class="{'dark bg-gray-900': darkMode === true}"
    class="bg-gray-50"
>
    <!-- Preloader -->
    <div 
        x-show="!loaded" 
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center bg-white dark:bg-gray-900"
    >
        <div class="flex items-center space-x-2">
            <div class="w-4 h-4 bg-blue-600 rounded-full animate-pulse"></div>
            <div class="w-4 h-4 bg-blue-600 rounded-full animate-pulse" style="animation-delay: 0.2s"></div>
            <div class="w-4 h-4 bg-blue-600 rounded-full animate-pulse" style="animation-delay: 0.4s"></div>
        </div>
    </div>

    <!-- Page Wrapper -->
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        @include('admin.partials.sidebar')

        <!-- Content Area -->
        <div class="relative flex flex-col flex-1 overflow-x-hidden overflow-y-auto">
            <!-- Mobile Overlay -->
            <div 
                x-show="sidebarToggle" 
                x-transition:enter="transition-opacity ease-linear duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition-opacity ease-linear duration-300"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="sidebarToggle = false"
                class="fixed inset-0 z-40 bg-black bg-opacity-50 lg:hidden"
            ></div>

            <!-- Header -->
            @include('admin.partials.header')

            <!-- Main Content -->
            <main class="flex-1">
                <div class="p-4 mx-auto max-w-7xl md:p-6">
                    <!-- Page Header -->
                    @if(isset($pageTitle) || isset($breadcrumbs))
                    <div class="mb-6">
                        @if(isset($pageTitle))
                        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                            {{ $pageTitle }}
                        </h1>
                        @endif
                        
                        @if(isset($breadcrumbs))
                        <nav class="flex mt-2" aria-label="Breadcrumb">
                            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                                @foreach($breadcrumbs as $breadcrumb)
                                <li class="inline-flex items-center">
                                    @if(!$loop->last)
                                    <a href="{{ $breadcrumb['url'] }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                                        {{ $breadcrumb['title'] }}
                                    </a>
                                    @if(!$loop->last)
                                    <svg class="w-3 h-3 mx-1 text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                                    </svg>
                                    @endif
                                    @else
                                    <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2 dark:text-gray-400">
                                        {{ $breadcrumb['title'] }}
                                    </span>
                                    @endif
                                </li>
                                @endforeach
                            </ol>
                        </nav>
                        @endif
                    </div>
                    @endif

                    <!-- Flash Messages -->
                    @if(session('success'))
                    <div x-data="{ show: true }" x-show="show" x-transition class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                        <div class="flex justify-between items-center">
                            <span>{{ session('success') }}</span>
                            <button @click="show = false" class="text-green-700 hover:text-green-900">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    @endif

                    @if(session('error'))
                    <div x-data="{ show: true }" x-show="show" x-transition class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                        <div class="flex justify-between items-center">
                            <span>{{ session('error') }}</span>
                            <button @click="show = false" class="text-red-700 hover:text-red-900">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    @endif

                    <!-- Page Content -->
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    @stack('scripts')
</body>
</html>
