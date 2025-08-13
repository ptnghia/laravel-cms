@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="grid grid-cols-12 gap-4 md:gap-6">
    <!-- Metrics Row -->
    <div class="col-span-12">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 md:gap-6 xl:grid-cols-4">
            <!-- Total Posts -->
            <div class="rounded-sm border border-gray-200 bg-white px-7.5 py-6 shadow-default dark:border-gray-800 dark:bg-black">
                <div class="flex h-11.5 w-11.5 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900">
                    <svg class="fill-blue-600 dark:fill-blue-400" width="22" height="16" viewBox="0 0 22 16" fill="none">
                        <path d="M11 15.1156C4.19376 15.1156 0.825012 8.61876 0.687512 8.34376C0.584387 8.13751 0.584387 7.86251 0.687512 7.65626C0.825012 7.38126 4.19376 0.918762 11 0.918762C17.8063 0.918762 21.175 7.38126 21.3125 7.65626C21.4156 7.86251 21.4156 8.13751 21.3125 8.34376C21.175 8.61876 17.8063 15.1156 11 15.1156ZM2.26876 8.00001C3.02501 9.27189 5.98126 13.5688 11 13.5688C16.0188 13.5688 18.975 9.27189 19.7313 8.00001C18.975 6.72814 16.0188 2.43126 11 2.43126C5.98126 2.43126 3.02501 6.72814 2.26876 8.00001Z" fill=""/>
                        <path d="M11 10.9219C9.38438 10.9219 8.07812 9.61562 8.07812 8C8.07812 6.38438 9.38438 5.07812 11 5.07812C12.6156 5.07812 13.9219 6.38438 13.9219 8C13.9219 9.61562 12.6156 10.9219 11 10.9219ZM11 6.625C10.2437 6.625 9.625 7.24375 9.625 8C9.625 8.75625 10.2437 9.375 11 9.375C11.7563 9.375 12.375 8.75625 12.375 8C12.375 7.24375 11.7563 6.625 11 6.625Z" fill=""/>
                    </svg>
                </div>

                <div class="mt-4 flex items-end justify-between">
                    <div>
                        <h4 class="text-title-md font-bold text-black dark:text-white">
                            {{ $stats['total_posts'] ?? 0 }}
                        </h4>
                        <span class="text-sm font-medium">Total Posts</span>
                    </div>

                    <span class="flex items-center gap-1 text-sm font-medium text-green-500">
                        0.43%
                        <svg class="fill-green-500" width="10" height="11" viewBox="0 0 10 11" fill="none">
                            <path d="M4.35716 2.47737L0.908974 5.82987L5.0443e-07 4.94612L5 0.0848689L10 4.94612L9.09103 5.82987L5.64284 2.47737L5.64284 10.0849L4.35716 10.0849L4.35716 2.47737Z" fill=""/>
                        </svg>
                    </span>
                </div>
            </div>

            <!-- Total Pages -->
            <div class="rounded-sm border border-gray-200 bg-white px-7.5 py-6 shadow-default dark:border-gray-800 dark:bg-black">
                <div class="flex h-11.5 w-11.5 items-center justify-center rounded-full bg-green-100 dark:bg-green-900">
                    <svg class="fill-green-600 dark:fill-green-400" width="18" height="18" viewBox="0 0 18 18" fill="none">
                        <path d="M15.7499 2.9812H14.2874V2.36245C14.2874 2.02495 14.0062 1.71558 13.6405 1.71558C13.2749 1.71558 12.9937 1.99683 12.9937 2.36245V2.9812H4.97803V2.36245C4.97803 2.02495 4.69678 1.71558 4.33115 1.71558C3.96553 1.71558 3.68428 1.99683 3.68428 2.36245V2.9812H2.2499C1.29365 2.9812 0.478027 3.7687 0.478027 4.75308V14.5406C0.478027 15.4968 1.26553 16.3125 2.2499 16.3125H15.7499C16.7062 16.3125 17.5218 15.525 17.5218 14.5406V4.72495C17.5218 3.7687 16.7062 2.9812 15.7499 2.9812ZM1.77178 8.21245H4.1624V10.9968H1.77178V8.21245ZM5.42803 8.21245H8.38115V10.9968H5.42803V8.21245ZM8.38115 12.2625V15.0187H5.42803V12.2625H8.38115ZM9.64678 8.21245H12.5999V10.9968H9.64678V8.21245ZM9.64678 12.2625H12.5999V15.0187H9.64678V12.2625ZM13.8374 8.21245H16.2281V10.9968H13.8374V8.21245ZM2.2499 4.24683H3.7124V4.83745C3.7124 5.17495 3.99365 5.48433 4.35928 5.48433C4.7249 5.48433 5.00615 5.20308 5.00615 4.83745V4.24683H13.0499V4.83745C13.0499 5.17495 13.3312 5.48433 13.6968 5.48433C14.0624 5.48433 14.3437 5.20308 14.3437 4.83745V4.24683H15.7499C16.0312 4.24683 16.2562 4.47183 16.2562 4.75308V6.94683H1.77178V4.75308C1.77178 4.47183 1.99678 4.24683 2.2499 4.24683ZM1.77178 14.5125V12.2343H4.1624V14.9906H2.2499C1.99678 15.0187 1.77178 14.7937 1.77178 14.5125ZM15.7499 15.0187H13.8374V12.2625H16.2281V14.5406C16.2562 14.7937 16.0312 15.0187 15.7499 15.0187Z" fill=""/>
                    </svg>
                </div>

                <div class="mt-4 flex items-end justify-between">
                    <div>
                        <h4 class="text-title-md font-bold text-black dark:text-white">
                            {{ $stats['total_pages'] ?? 0 }}
                        </h4>
                        <span class="text-sm font-medium">Total Pages</span>
                    </div>

                    <span class="flex items-center gap-1 text-sm font-medium text-green-500">
                        4.35%
                        <svg class="fill-green-500" width="10" height="11" viewBox="0 0 10 11" fill="none">
                            <path d="M4.35716 2.47737L0.908974 5.82987L5.0443e-07 4.94612L5 0.0848689L10 4.94612L9.09103 5.82987L5.64284 2.47737L5.64284 10.0849L4.35716 10.0849L4.35716 2.47737Z" fill=""/>
                        </svg>
                    </span>
                </div>
            </div>

            <!-- Total Users -->
            <div class="rounded-sm border border-gray-200 bg-white px-7.5 py-6 shadow-default dark:border-gray-800 dark:bg-black">
                <div class="flex h-11.5 w-11.5 items-center justify-center rounded-full bg-yellow-100 dark:bg-yellow-900">
                    <svg class="fill-yellow-600 dark:fill-yellow-400" width="18" height="18" viewBox="0 0 18 18" fill="none">
                        <path d="M9.0002 7.79065C11.0814 7.79065 12.7689 6.1594 12.7689 4.1344C12.7689 2.1094 11.0814 0.478149 9.0002 0.478149C6.91895 0.478149 5.23145 2.1094 5.23145 4.1344C5.23145 6.1594 6.91895 7.79065 9.0002 7.79065ZM9.0002 1.7719C10.3783 1.7719 11.5033 2.84065 11.5033 4.16252C11.5033 5.4844 10.3783 6.55315 9.0002 6.55315C7.62207 6.55315 6.49707 5.4844 6.49707 4.16252C6.49707 2.84065 7.62207 1.7719 9.0002 1.7719Z" fill=""/>
                        <path d="M10.8283 9.05627H7.17207C4.16269 9.05627 1.71582 11.5313 1.71582 14.5406V16.875C1.71582 17.2125 1.99707 17.5219 2.36269 17.5219C2.72832 17.5219 3.00957 17.2407 3.00957 16.875V14.5406C3.00957 12.2344 4.89394 10.3219 7.22832 10.3219H10.8564C13.1627 10.3219 15.0752 12.2063 15.0752 14.5406V16.875C15.0752 17.2125 15.3564 17.5219 15.7221 17.5219C16.0877 17.5219 16.3689 17.2407 16.3689 16.875V14.5406C16.2846 11.5313 13.8377 9.05627 10.8283 9.05627Z" fill=""/>
                    </svg>
                </div>

                <div class="mt-4 flex items-end justify-between">
                    <div>
                        <h4 class="text-title-md font-bold text-black dark:text-white">
                            {{ $stats['total_users'] ?? 0 }}
                        </h4>
                        <span class="text-sm font-medium">Total Users</span>
                    </div>

                    <span class="flex items-center gap-1 text-sm font-medium text-red-500">
                        -2.14%
                        <svg class="fill-red-500" width="10" height="11" viewBox="0 0 10 11" fill="none">
                            <path d="M5.64284 7.69237L9.09102 4.33987L10 5.22362L5 10.0849L-8.98488e-07 5.22362L0.908973 4.33987L4.35716 7.69237L4.35716 0.0848701L5.64284 0.0848704L5.64284 7.69237Z" fill=""/>
                        </svg>
                    </span>
                </div>
            </div>

            <!-- Total Media -->
            <div class="rounded-sm border border-gray-200 bg-white px-7.5 py-6 shadow-default dark:border-gray-800 dark:bg-black">
                <div class="flex h-11.5 w-11.5 items-center justify-center rounded-full bg-purple-100 dark:bg-purple-900">
                    <svg class="fill-purple-600 dark:fill-purple-400" width="18" height="18" viewBox="0 0 18 18" fill="none">
                        <path d="M15.7499 0.937744H2.2499C1.29365 0.937744 0.478027 1.75337 0.478027 2.70962V15.2096C0.478027 16.1659 1.29365 16.9815 2.2499 16.9815H15.7499C16.7062 16.9815 17.5218 16.1659 17.5218 15.2096V2.70962C17.5218 1.75337 16.7062 0.937744 15.7499 0.937744ZM16.2281 15.2096C16.2281 15.4627 16.0031 15.6877 15.7499 15.6877H2.2499C1.99678 15.6877 1.77178 15.4627 1.77178 15.2096V2.70962C1.77178 2.4565 1.99678 2.2315 2.2499 2.2315H15.7499C16.0031 2.2315 16.2281 2.4565 16.2281 2.70962V15.2096Z" fill=""/>
                        <path d="M4.85605 7.7971C5.7748 7.7971 6.51855 7.05335 6.51855 6.1346C6.51855 5.21585 5.7748 4.4721 4.85605 4.4721C3.9373 4.4721 3.19355 5.21585 3.19355 6.1346C3.19355 7.05335 3.9373 7.7971 4.85605 7.7971ZM4.85605 5.7659C5.06105 5.7659 5.22480 5.9284 5.22480 6.1346C5.22480 6.3408 5.06105 6.5033 4.85605 6.5033C4.65105 6.5033 4.4873 6.3408 4.4873 6.1346C4.4873 5.9284 4.65105 5.7659 4.85605 5.7659Z" fill=""/>
                        <path d="M3.53857 14.0315C3.19482 14.0315 2.91357 13.7503 2.91357 13.4065C2.91357 13.0628 3.19482 12.7815 3.53857 12.7815C3.88232 12.7815 4.16357 13.0628 4.16357 13.4065C4.16357 13.7503 3.88232 14.0315 3.53857 14.0315Z" fill=""/>
                        <path d="M14.4614 14.0315H6.51855C6.17480 14.0315 5.89355 13.7503 5.89355 13.4065C5.89355 13.0628 6.17480 12.7815 6.51855 12.7815H14.4614C14.8052 12.7815 15.0864 13.0628 15.0864 13.4065C15.0864 13.7503 14.8052 14.0315 14.4614 14.0315Z" fill=""/>
                        <path d="M14.4614 11.5471H3.53857C3.19482 11.5471 2.91357 11.2659 2.91357 10.9221C2.91357 10.5784 3.19482 10.2971 3.53857 10.2971H14.4614C14.8052 10.2971 15.0864 10.5784 15.0864 10.9221C15.0864 11.2659 14.8052 11.5471 14.4614 11.5471Z" fill=""/>
                    </svg>
                </div>

                <div class="mt-4 flex items-end justify-between">
                    <div>
                        <h4 class="text-title-md font-bold text-black dark:text-white">
                            {{ $stats['total_media'] ?? 0 }}
                        </h4>
                        <span class="text-sm font-medium">Media Files</span>
                    </div>

                    <span class="flex items-center gap-1 text-sm font-medium text-green-500">
                        1.8%
                        <svg class="fill-green-500" width="10" height="11" viewBox="0 0 10 11" fill="none">
                            <path d="M4.35716 2.47737L0.908974 5.82987L5.0443e-07 4.94612L5 0.0848689L10 4.94612L9.09103 5.82987L5.64284 2.47737L5.64284 10.0849L4.35716 10.0849L4.35716 2.47737Z" fill=""/>
                        </svg>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="col-span-12 xl:col-span-8">
        <div class="rounded-sm border border-gray-200 bg-white px-5 pt-6 pb-2.5 shadow-default dark:border-gray-800 dark:bg-black sm:px-7.5 xl:pb-1">
            <h4 class="mb-6 text-xl font-semibold text-black dark:text-white">
                Recent Activity
            </h4>

            <div class="flex flex-col">
                <div class="grid grid-cols-3 rounded-sm bg-gray-50 dark:bg-gray-800 sm:grid-cols-5">
                    <div class="p-2.5 xl:p-5">
                        <h5 class="text-sm font-medium uppercase xsm:text-base">
                            Type
                        </h5>
                    </div>
                    <div class="p-2.5 text-center xl:p-5">
                        <h5 class="text-sm font-medium uppercase xsm:text-base">
                            Title
                        </h5>
                    </div>
                    <div class="p-2.5 text-center xl:p-5">
                        <h5 class="text-sm font-medium uppercase xsm:text-base">
                            Author
                        </h5>
                    </div>
                    <div class="hidden p-2.5 text-center sm:block xl:p-5">
                        <h5 class="text-sm font-medium uppercase xsm:text-base">
                            Status
                        </h5>
                    </div>
                    <div class="hidden p-2.5 text-center sm:block xl:p-5">
                        <h5 class="text-sm font-medium uppercase xsm:text-base">
                            Date
                        </h5>
                    </div>
                </div>

                @forelse($recent_activity ?? [] as $activity)
                <div class="grid grid-cols-3 border-b border-gray-200 dark:border-gray-800 sm:grid-cols-5">
                    <div class="flex items-center gap-3 p-2.5 xl:p-5">
                        <div class="flex-shrink-0">
                            @if($activity['type'] === 'post')
                            <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            @elseif($activity['type'] === 'page')
                            <div class="h-8 w-8 rounded-full bg-green-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            @else
                            <div class="h-8 w-8 rounded-full bg-gray-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            @endif
                        </div>
                        <p class="text-black dark:text-white">{{ ucfirst($activity['type']) }}</p>
                    </div>

                    <div class="flex items-center justify-center p-2.5 xl:p-5">
                        <p class="text-black dark:text-white">{{ $activity['title'] }}</p>
                    </div>

                    <div class="flex items-center justify-center p-2.5 xl:p-5">
                        <p class="text-black dark:text-white">{{ $activity['author'] }}</p>
                    </div>

                    <div class="hidden items-center justify-center p-2.5 sm:flex xl:p-5">
                        <span class="inline-flex rounded-full bg-{{ $activity['status'] === 'published' ? 'green' : 'yellow' }}-100 px-3 py-1 text-xs font-medium text-{{ $activity['status'] === 'published' ? 'green' : 'yellow' }}-800">
                            {{ ucfirst($activity['status']) }}
                        </span>
                    </div>

                    <div class="hidden items-center justify-center p-2.5 sm:flex xl:p-5">
                        <p class="text-black dark:text-white">{{ $activity['date'] }}</p>
                    </div>
                </div>
                @empty
                <div class="p-8 text-center">
                    <p class="text-gray-500 dark:text-gray-400">No recent activity found.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="col-span-12 xl:col-span-4">
        <div class="rounded-sm border border-gray-200 bg-white p-6 shadow-default dark:border-gray-800 dark:bg-black">
            <h4 class="mb-6 text-xl font-semibold text-black dark:text-white">
                Quick Actions
            </h4>

            <div class="space-y-4">
                <a href="{{ route('admin.posts.create') }}" class="flex w-full items-center justify-center rounded-md bg-blue-600 px-4 py-3 text-white hover:bg-blue-700 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Create New Post
                </a>

                <a href="{{ route('admin.pages.create') }}" class="flex w-full items-center justify-center rounded-md bg-green-600 px-4 py-3 text-white hover:bg-green-700 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Create New Page
                </a>

                <a href="{{ route('admin.media.index') }}" class="flex w-full items-center justify-center rounded-md bg-purple-600 px-4 py-3 text-white hover:bg-purple-700 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    Manage Media
                </a>

                <a href="{{ route('admin.users.index') }}" class="flex w-full items-center justify-center rounded-md bg-yellow-600 px-4 py-3 text-white hover:bg-yellow-700 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                    Manage Users
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
