<aside
    :class="sidebarToggle ? 'translate-x-0 lg:w-[90px]' : '-translate-x-full'"
    class="sidebar fixed left-0 top-0 z-50 flex h-screen w-[290px] flex-col overflow-y-hidden border-r border-gray-200 bg-white px-5 dark:border-gray-800 dark:bg-black lg:static lg:translate-x-0"
>
    <!-- SIDEBAR HEADER -->
    <div
        :class="sidebarToggle ? 'justify-center' : 'justify-between'"
        class="flex items-center gap-2 pt-8 sidebar-header pb-7"
    >
        <a href="{{ route('admin.dashboard') }}">
            <span class="logo" :class="sidebarToggle ? 'hidden' : ''">
                <img class="h-8 dark:hidden" src="{{ asset('admin/images/logo/logo.svg') }}" alt="Laravel CMS" />
                <img
                    class="hidden h-8 dark:block"
                    src="{{ asset('admin/images/logo/logo-dark.svg') }}"
                    alt="Laravel CMS"
                />
            </span>

            <img
                class="logo-icon h-8"
                :class="sidebarToggle ? 'lg:block' : 'hidden'"
                src="{{ asset('admin/images/logo/logo-icon.svg') }}"
                alt="Laravel CMS"
            />
        </a>

        <!-- Sidebar Toggle Button -->
        <button
            @click="sidebarToggle = !sidebarToggle"
            class="lg:hidden p-2 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-800 dark:hover:text-gray-300"
        >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>
    <!-- SIDEBAR HEADER -->

    <div class="flex flex-col overflow-y-auto duration-300 ease-linear no-scrollbar">
        <!-- Sidebar Menu -->
        <nav x-data="{selected: $persist('{{ request()->route()->getName() ?? 'dashboard' }}')}">
            <!-- Menu Group -->
            <div>
                <h3 class="mb-4 text-xs uppercase leading-[20px] text-gray-400">
                    <span
                        class="menu-group-title"
                        :class="sidebarToggle ? 'lg:hidden' : ''"
                    >
                        MENU
                    </span>
                </h3>

                <ul class="mb-6 flex flex-col gap-1.5">
                    <!-- Dashboard -->
                    <li>
                        <a
                            href="{{ route('admin.dashboard') }}"
                            @click="selected = 'admin.dashboard'"
                            :class="selected === 'admin.dashboard' ? 'bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400' : 'text-gray-600 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-800'"
                            class="group relative flex items-center gap-2.5 rounded-md px-4 py-2 font-medium duration-300 ease-in-out"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v2H8V5z"></path>
                            </svg>
                            <span :class="sidebarToggle ? 'lg:hidden' : ''">Dashboard</span>
                        </a>
                    </li>

                    <!-- Content Management -->
                    <li x-data="{ open: false }">
                        <button
                            @click="open = !open"
                            class="group relative flex w-full items-center gap-2.5 rounded-md px-4 py-2 font-medium text-gray-600 duration-300 ease-in-out hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-800"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <span :class="sidebarToggle ? 'lg:hidden' : ''">Content</span>
                            <svg
                                :class="open ? 'rotate-180' : ''"
                                class="absolute right-4 top-1/2 -translate-y-1/2 fill-current transition-transform duration-200"
                                width="20"
                                height="20"
                                viewBox="0 0 20 20"
                                fill="none"
                            >
                                <path
                                    fill-rule="evenodd"
                                    clip-rule="evenodd"
                                    d="M4.41107 6.9107C4.73651 6.58527 5.26414 6.58527 5.58958 6.9107L10.0003 11.3214L14.4111 6.91071C14.7365 6.58527 15.2641 6.58527 15.5896 6.91071C15.915 7.23614 15.915 7.76378 15.5896 8.08922L10.5896 13.0892C10.2641 13.4147 9.73651 13.4147 9.41107 13.0892L4.41107 8.08922C4.08563 7.76378 4.08563 7.23614 4.41107 6.9107Z"
                                    fill=""
                                />
                            </svg>
                        </button>

                        <div x-show="open" x-transition class="translate transform overflow-hidden">
                            <ul class="mt-4 mb-5.5 flex flex-col gap-2.5 pl-6">
                                <li>
                                    <a
                                        href="{{ route('admin.posts.index') }}"
                                        @click="selected = 'admin.posts.index'"
                                        :class="selected === 'admin.posts.index' ? 'text-blue-600 dark:text-blue-400' : 'text-gray-600 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400'"
                                        class="group relative flex items-center gap-2.5 rounded-md px-4 font-medium duration-300 ease-in-out"
                                    >
                                        Posts
                                    </a>
                                </li>
                                <li>
                                    <a
                                        href="{{ route('admin.pages.index') }}"
                                        @click="selected = 'admin.pages.index'"
                                        :class="selected === 'admin.pages.index' ? 'text-blue-600 dark:text-blue-400' : 'text-gray-600 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400'"
                                        class="group relative flex items-center gap-2.5 rounded-md px-4 font-medium duration-300 ease-in-out"
                                    >
                                        Pages
                                    </a>
                                </li>
                                <li>
                                    <a
                                        href="{{ route('admin.categories.index') }}"
                                        @click="selected = 'admin.categories.index'"
                                        :class="selected === 'admin.categories.index' ? 'text-blue-600 dark:text-blue-400' : 'text-gray-600 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400'"
                                        class="group relative flex items-center gap-2.5 rounded-md px-4 font-medium duration-300 ease-in-out"
                                    >
                                        Categories
                                    </a>
                                </li>
                                <li>
                                    <a
                                        href="{{ route('admin.tags.index') }}"
                                        @click="selected = 'admin.tags.index'"
                                        :class="selected === 'admin.tags.index' ? 'text-blue-600 dark:text-blue-400' : 'text-gray-600 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400'"
                                        class="group relative flex items-center gap-2.5 rounded-md px-4 font-medium duration-300 ease-in-out"
                                    >
                                        Tags
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <!-- Media -->
                    <li>
                        <a
                            href="{{ route('admin.media.index') }}"
                            @click="selected = 'admin.media.index'"
                            :class="selected === 'admin.media.index' ? 'bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400' : 'text-gray-600 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-800'"
                            class="group relative flex items-center gap-2.5 rounded-md px-4 py-2 font-medium duration-300 ease-in-out"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span :class="sidebarToggle ? 'lg:hidden' : ''">Media</span>
                        </a>
                    </li>

                    <!-- Users -->
                    <li>
                        <a
                            href="{{ route('admin.users.index') }}"
                            @click="selected = 'admin.users.index'"
                            :class="selected === 'admin.users.index' ? 'bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400' : 'text-gray-600 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-800'"
                            class="group relative flex items-center gap-2.5 rounded-md px-4 py-2 font-medium duration-300 ease-in-out"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                            <span :class="sidebarToggle ? 'lg:hidden' : ''">Users</span>
                        </a>
                    </li>

                    <!-- Settings -->
                    <li>
                        <a
                            href="{{ route('admin.settings.index') }}"
                            @click="selected = 'admin.settings.index'"
                            :class="selected === 'admin.settings.index' ? 'bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400' : 'text-gray-600 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-800'"
                            class="group relative flex items-center gap-2.5 rounded-md px-4 py-2 font-medium duration-300 ease-in-out"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span :class="sidebarToggle ? 'lg:hidden' : ''">Settings</span>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
</aside>
