<header class="sticky top-0 z-40 flex w-full bg-white border-b border-gray-200 dark:bg-black dark:border-gray-800">
    <div class="flex flex-grow items-center justify-between px-4 py-4 shadow-2 md:px-6">
        <!-- Left Side -->
        <div class="flex items-center gap-2 sm:gap-4">
            <!-- Sidebar Toggle -->
            <button
                @click="sidebarToggle = !sidebarToggle"
                class="z-50 block rounded-sm border border-gray-200 bg-white p-1.5 shadow-sm dark:border-gray-800 dark:bg-black lg:hidden"
            >
                <span class="relative block h-5.5 w-5.5 cursor-pointer">
                    <span class="du-block absolute right-0 h-full w-full">
                        <span
                            :class="!sidebarToggle ? 'delay-300' : 'delay-400'"
                            class="relative left-0 top-0 my-1 block h-0.5 w-0 rounded-sm bg-black delay-300 duration-200 ease-in-out dark:bg-white"
                            :class="!sidebarToggle ? '!w-full delay-300' : 'w-0 delay-400'"
                        ></span>
                        <span
                            class="relative left-0 top-0 my-1 block h-0.5 w-0 rounded-sm bg-black delay-150 duration-200 ease-in-out dark:bg-white"
                            :class="!sidebarToggle ? 'delay-400 !w-full' : '!w-full delay-200'"
                        ></span>
                        <span
                            :class="!sidebarToggle ? 'delay-500' : 'delay-200'"
                            class="relative left-0 top-0 my-1 block h-0.5 w-0 rounded-sm bg-black delay-200 duration-200 ease-in-out dark:bg-white"
                            :class="!sidebarToggle ? '!w-full delay-500' : 'w-0 delay-200'"
                        ></span>
                    </span>
                    <span class="absolute right-0 h-full w-full rotate-45">
                        <span
                            :class="!sidebarToggle ? 'delay-300' : 'delay-400'"
                            class="absolute left-2.5 top-0 block h-full w-0.5 rounded-sm bg-black delay-300 duration-200 ease-in-out dark:bg-white"
                            :class="!sidebarToggle ? '!h-0 !delay-300' : 'h-full delay-400'"
                        ></span>
                        <span
                            :class="!sidebarToggle ? 'delay-400' : 'delay-200'"
                            class="delay-400 absolute left-0 top-2.5 block h-0.5 w-full rounded-sm bg-black duration-200 ease-in-out dark:bg-white"
                            :class="!sidebarToggle ? '!h-0.5 delay-400' : '!h-0 delay-200'"
                        ></span>
                    </span>
                </span>
            </button>

            <!-- Search Form -->
            <form class="hidden sm:block" action="#" method="GET">
                <div class="relative">
                    <button class="absolute left-0 top-1/2 -translate-y-1/2">
                        <svg
                            class="fill-body hover:fill-primary dark:fill-bodydark dark:hover:fill-primary"
                            width="20"
                            height="20"
                            viewBox="0 0 20 20"
                            fill="none"
                        >
                            <path
                                fill-rule="evenodd"
                                clip-rule="evenodd"
                                d="M9.16666 3.33332C5.945 3.33332 3.33332 5.945 3.33332 9.16666C3.33332 12.3883 5.945 15 9.16666 15C12.3883 15 15 12.3883 15 9.16666C15 5.945 12.3883 3.33332 9.16666 3.33332ZM1.66666 9.16666C1.66666 5.02452 5.02452 1.66666 9.16666 1.66666C13.3088 1.66666 16.6667 5.02452 16.6667 9.16666C16.6667 13.3088 13.3088 16.6667 9.16666 16.6667C5.02452 16.6667 1.66666 13.3088 1.66666 9.16666Z"
                                fill=""
                            />
                            <path
                                fill-rule="evenodd"
                                clip-rule="evenodd"
                                d="M13.2857 13.2857C13.6112 12.9603 14.1388 12.9603 14.4642 13.2857L18.0892 16.9107C18.4147 17.2362 18.4147 17.7638 18.0892 18.0892C17.7638 18.4147 17.2362 18.4147 16.9107 18.0892L13.2857 14.4642C12.9603 14.1388 12.9603 13.6112 13.2857 13.2857Z"
                                fill=""
                            />
                        </svg>
                    </button>

                    <input
                        type="text"
                        name="search"
                        placeholder="Type to search..."
                        class="w-full bg-transparent pl-9 pr-4 text-black focus:outline-none dark:text-white xl:w-125"
                    />
                </div>
            </form>
        </div>

        <!-- Right Side -->
        <div class="flex items-center gap-3 2xsm:gap-7">
            <!-- Dark Mode Toggle -->
            <div>
                <label
                    :class="darkMode ? 'bg-primary' : 'bg-stroke'"
                    class="relative m-0 block h-7.5 w-14 rounded-full bg-stroke p-1 duration-300 ease-in-out dark:bg-[#5A616B]"
                >
                    <input
                        type="checkbox"
                        :value="darkMode"
                        @change="darkMode = !darkMode"
                        class="dur absolute top-0 z-50 m-0 h-full w-full cursor-pointer opacity-0"
                    />
                    <span
                        :class="darkMode && '!right-1 !translate-x-full'"
                        class="absolute left-1 top-1/2 flex h-6 w-6 -translate-y-1/2 translate-x-0 items-center justify-center rounded-full bg-white shadow-switcher duration-75 ease-linear"
                    >
                        <span class="dark:hidden">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path
                                    d="M7.99998 11.9999C10.2091 11.9999 12 10.2091 12 7.99993C12 5.79075 10.2091 3.99993 7.99998 3.99993C5.79081 3.99993 3.99998 5.79075 3.99998 7.99993C3.99998 10.2091 5.79081 11.9999 7.99998 11.9999Z"
                                    fill="#969AA1"
                                />
                                <path
                                    d="M8.00002 15.3941C7.69669 15.3941 7.44669 15.1441 7.44669 14.8408V14.1991C7.44669 13.8958 7.69669 13.6458 8.00002 13.6458C8.30335 13.6458 8.55335 13.8958 8.55335 14.1991V14.8408C8.55335 15.1441 8.30335 15.3941 8.00002 15.3941Z"
                                    fill="#969AA1"
                                />
                                <path
                                    d="M8.00002 2.35409C7.69669 2.35409 7.44669 2.10409 7.44669 1.80076V1.15909C7.44669 0.855756 7.69669 0.605756 8.00002 0.605756C8.30335 0.605756 8.55335 0.855756 8.55335 1.15909V1.80076C8.55335 2.10409 8.30335 2.35409 8.00002 2.35409Z"
                                    fill="#969AA1"
                                />
                                <path
                                    d="M15.3941 8.55335H14.7525C14.4491 8.55335 14.1991 8.30335 14.1991 8.00002C14.1991 7.69669 14.4491 7.44669 14.7525 7.44669H15.3941C15.6975 7.44669 15.9475 7.69669 15.9475 8.00002C15.9475 8.30335 15.6975 8.55335 15.3941 8.55335Z"
                                    fill="#969AA1"
                                />
                                <path
                                    d="M1.24754 8.55335H0.605874C0.302541 8.55335 0.0525408 8.30335 0.0525408 8.00002C0.0525408 7.69669 0.302541 7.44669 0.605874 7.44669H1.24754C1.55087 7.44669 1.80087 7.69669 1.80087 8.00002C1.80087 8.30335 1.55087 8.55335 1.24754 8.55335Z"
                                    fill="#969AA1"
                                />
                                <path
                                    d="M13.1666 3.83331C12.9758 3.83331 12.785 3.76081 12.6408 3.61664C12.3525 3.32831 12.3525 2.85164 12.6408 2.56331L13.0491 2.15498C13.3375 1.86664 13.8141 1.86664 14.1025 2.15498C14.3908 2.44331 14.3908 2.91998 14.1025 3.20831L13.6941 3.61664C13.55 3.76081 13.3591 3.83331 13.1666 3.83331Z"
                                    fill="#969AA1"
                                />
                                <path
                                    d="M2.95081 13.8441C2.75998 13.8441 2.56915 13.7716 2.42498 13.6274C2.13665 13.3391 2.13665 12.8624 2.42498 12.5741L2.83331 12.1658C3.12165 11.8774 3.59831 11.8774 3.88665 12.1658C4.17498 12.4541 4.17498 12.9308 3.88665 13.2191L3.47831 13.6274C3.33415 13.7716 3.14331 13.8441 2.95081 13.8441Z"
                                    fill="#969AA1"
                                />
                                <path
                                    d="M13.1666 13.8441C12.9758 13.8441 12.785 13.7716 12.6408 13.6274L12.2325 13.2191C11.9441 12.9308 11.9441 12.4541 12.2325 12.1658C12.5208 11.8774 12.9975 11.8774 13.2858 12.1658L13.6941 12.5741C13.9825 12.8624 13.9825 13.3391 13.6941 13.6274C13.55 13.7716 13.3591 13.8441 13.1666 13.8441Z"
                                    fill="#969AA1"
                                />
                                <path
                                    d="M2.95081 3.83331C2.75998 3.83331 2.56915 3.76081 2.42498 3.61664L2.01665 3.20831C1.72831 2.91998 1.72831 2.44331 2.01665 2.15498C2.30498 1.86664 2.78165 1.86664 3.06998 2.15498L3.47831 2.56331C3.76665 2.85164 3.76665 3.32831 3.47831 3.61664C3.33415 3.76081 3.14331 3.83331 2.95081 3.83331Z"
                                    fill="#969AA1"
                                />
                            </svg>
                        </span>
                        <span class="hidden dark:inline-block">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path
                                    d="M14.3533 10.62C14.2466 10.44 13.9466 10.16 13.1999 10.2933C12.7866 10.3667 12.3666 10.4 11.9466 10.38C10.3933 10.3133 8.98659 9.6 8.00659 8.5C7.13993 7.53333 6.60659 6.27333 6.59993 4.91333C6.59993 4.15333 6.74659 3.42 7.04659 2.72666C7.33993 2.05333 7.13326 1.7 6.98659 1.55333C6.83326 1.4 6.47326 1.18666 5.76659 1.48C3.03993 2.62666 1.35326 5.36 1.55326 8.28666C1.75326 11.04 3.68659 13.3933 6.24659 14.28C6.85993 14.4933 7.50659 14.6067 8.17326 14.6067C8.27993 14.6067 8.38659 14.6067 8.49326 14.6C10.7266 14.4867 12.8199 13.4867 14.3466 11.8133C14.9533 11.1533 14.5866 10.9133 14.3533 10.62Z"
                                    fill="#969AA1"
                                />
                            </svg>
                        </span>
                    </span>
                </label>
            </div>

            <!-- Notification Dropdown -->
            <div x-data="{ open: false }" class="relative">
                <button
                    @click="open = !open"
                    class="relative flex h-8.5 w-8.5 items-center justify-center rounded-full border-[0.5px] border-gray-200 bg-gray-50 hover:text-primary dark:border-gray-800 dark:bg-gray-800 dark:text-white"
                >
                    <span class="absolute -top-0.5 -right-0.5 z-1 h-2 w-2 rounded-full bg-red-500">
                        <span class="absolute -z-1 inline-flex h-full w-full animate-ping rounded-full bg-red-400 opacity-75"></span>
                    </span>

                    <svg class="fill-current duration-300 ease-in-out" width="18" height="18" viewBox="0 0 18 18" fill="none">
                        <path
                            d="M16.1999 14.9343L15.6374 14.0624C15.5249 13.8937 15.4687 13.7249 15.4687 13.528V7.67803C15.4687 6.01865 14.7655 4.47178 13.4718 3.31865C12.4312 2.39053 11.0812 1.7999 9.64678 1.6874V1.1249C9.64678 0.787402 9.36553 0.478027 8.9999 0.478027C8.6624 0.478027 8.35303 0.759277 8.35303 1.1249V1.65928C8.29678 1.65928 8.24053 1.65928 8.18428 1.6874C4.92178 2.05303 2.4749 4.66865 2.4749 7.79053V13.528C2.44678 13.8093 2.39053 13.9499 2.33428 14.0343L1.7999 14.9343C1.63115 15.2155 1.63115 15.553 1.7999 15.8343C1.96865 16.0874 2.2499 16.2562 2.55928 16.2562H8.38115V16.8749C8.38115 17.2124 8.6624 17.5218 9.02803 17.5218C9.36553 17.5218 9.6749 17.2405 9.6749 16.8749V16.2562H15.4687C15.778 16.2562 16.0593 16.0874 16.228 15.8343C16.3968 15.553 16.3968 15.2155 16.1999 14.9343ZM3.23428 14.9905L3.43115 14.653C3.5999 14.3718 3.68428 14.0343 3.74053 13.6405V7.79053C3.74053 5.31553 5.70928 3.23428 8.3249 2.95303C9.92803 2.78428 11.503 3.2624 12.6562 4.2749C13.6687 5.1749 14.2312 6.38428 14.2312 7.67803V13.528C14.2312 13.9499 14.3437 14.3437 14.5968 14.7374L14.7655 14.9905H3.23428Z"
                            fill=""
                        />
                    </svg>
                </button>

                <!-- Notification Dropdown -->
                <div
                    x-show="open"
                    @click.outside="open = false"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="absolute right-0 mt-2.5 flex h-90 w-75 flex-col rounded-sm border border-gray-200 bg-white shadow-default dark:border-gray-800 dark:bg-black sm:right-0 sm:w-80"
                >
                    <div class="px-4.5 py-3">
                        <h5 class="text-sm font-medium text-gray-900 dark:text-white">Notification</h5>
                    </div>

                    <ul class="flex h-auto flex-col overflow-y-auto">
                        <li>
                            <a class="flex flex-col gap-2.5 border-t border-gray-200 px-4.5 py-3 hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-800" href="#">
                                <p class="text-sm text-gray-900 dark:text-white">
                                    <span class="text-black dark:text-white">Edit your information in a swipe</span>
                                    Sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim.
                                </p>

                                <p class="text-xs text-gray-500">12 May, 2025</p>
                            </a>
                        </li>
                        <li>
                            <a class="flex flex-col gap-2.5 border-t border-gray-200 px-4.5 py-3 hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-800" href="#">
                                <p class="text-sm text-gray-900 dark:text-white">
                                    <span class="text-black dark:text-white">It is a long established fact</span>
                                    that a reader will be distracted by the readable content of a page when looking at its layout.
                                </p>

                                <p class="text-xs text-gray-500">24 Feb, 2025</p>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- User Dropdown -->
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="flex items-center gap-4">
                    <span class="hidden text-right lg:block">
                        <span class="block text-sm font-medium text-black dark:text-white">{{ auth()->user()->name ?? 'Admin User' }}</span>
                        <span class="block text-xs text-gray-500">{{ auth()->user()->email ?? 'admin@example.com' }}</span>
                    </span>

                    <span class="h-12 w-12 rounded-full">
                        <div class="h-12 w-12 rounded-full bg-blue-600 flex items-center justify-center text-white font-medium">
                            {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
                        </div>
                    </span>

                    <svg
                        :class="open && 'rotate-180'"
                        class="hidden fill-current sm:block"
                        width="12"
                        height="8"
                        viewBox="0 0 12 8"
                        fill="none"
                    >
                        <path
                            fill-rule="evenodd"
                            clip-rule="evenodd"
                            d="M0.410765 0.910734C0.736202 0.585297 1.26384 0.585297 1.58928 0.910734L6.00002 5.32148L10.4108 0.910734C10.7362 0.585297 11.2638 0.585297 11.5893 0.910734C11.9147 1.23617 11.9147 1.76381 11.5893 2.08924L6.58928 7.08924C6.26384 7.41468 5.7362 7.41468 5.41077 7.08924L0.410765 2.08924C0.0853277 1.76381 0.0853277 1.23617 0.410765 0.910734Z"
                            fill=""
                        />
                    </svg>
                </button>

                <!-- User Dropdown Menu -->
                <div
                    x-show="open"
                    @click.outside="open = false"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="absolute right-0 mt-4 flex w-62.5 flex-col rounded-sm border border-gray-200 bg-white shadow-default dark:border-gray-800 dark:bg-black"
                >
                    <ul class="flex flex-col gap-5 border-b border-gray-200 px-6 py-7.5 dark:border-gray-800">
                        <li>
                            <a href="{{ route('admin.profile.edit') }}" class="flex items-center gap-3.5 text-sm font-medium duration-300 ease-in-out hover:text-primary lg:text-base">
                                <svg class="fill-current" width="22" height="22" viewBox="0 0 22 22" fill="none">
                                    <path
                                        d="M11 9.62499C8.42188 9.62499 6.35938 7.59687 6.35938 5.12187C6.35938 2.64687 8.42188 0.618744 11 0.618744C13.5781 0.618744 15.6406 2.64687 15.6406 5.12187C15.6406 7.59687 13.5781 9.62499 11 9.62499ZM11 2.16562C9.28125 2.16562 7.90625 3.50624 7.90625 5.12187C7.90625 6.73749 9.28125 8.07812 11 8.07812C12.7188 8.07812 14.0938 6.73749 14.0938 5.12187C14.0938 3.50624 12.7188 2.16562 11 2.16562Z"
                                        fill=""
                                    />
                                    <path
                                        d="M17.7719 21.4156H4.2281C3.5406 21.4156 2.9906 20.8656 2.9906 20.1781V17.0844C2.9906 13.7156 5.7406 10.9656 9.10935 10.9656H12.925C16.2937 10.9656 19.0437 13.7156 19.0437 17.0844V20.1781C19.0437 20.8656 18.4937 21.4156 17.7719 21.4156ZM4.53748 19.8687H17.4969V17.0844C17.4969 14.575 15.4344 12.5125 12.925 12.5125H9.10935C6.59998 12.5125 4.53748 14.575 4.53748 17.0844V19.8687Z"
                                        fill=""
                                    />
                                </svg>
                                My Profile
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.settings.index') }}" class="flex items-center gap-3.5 text-sm font-medium duration-300 ease-in-out hover:text-primary lg:text-base">
                                <svg class="fill-current" width="22" height="22" viewBox="0 0 22 22" fill="none">
                                    <path
                                        d="M20.8656 8.86874C20.5219 8.49062 20.0406 8.28437 19.525 8.28437H19.4219C19.25 8.28437 19.1125 8.18124 19.0781 8.04374C19.0437 7.90624 18.975 7.80312 18.9406 7.66562C18.8719 7.52812 18.9406 7.39062 19.0437 7.28749L19.1125 7.21874C19.4906 6.87499 19.6969 6.39374 19.6969 5.87812C19.6969 5.36249 19.4906 4.88124 19.1125 4.53749L17.4625 2.88749C16.7406 2.16562 15.6125 2.16562 14.8906 2.88749L14.8219 2.95624C14.7188 3.05937 14.5812 3.12812 14.4437 3.05937C14.3062 2.99062 14.2031 2.92187 14.0656 2.88749C13.9281 2.85312 13.825 2.71562 13.825 2.54374V2.44062C13.825 1.40937 12.9969 0.581244 11.9656 0.581244H9.76873C8.73748 0.581244 7.90935 1.40937 7.90935 2.44062V2.54374C7.90935 2.71562 7.80623 2.85312 7.66873 2.88749C7.53123 2.92187 7.42811 2.99062 7.29061 3.05937C7.15311 3.12812 7.01561 3.05937 6.91248 2.95624L6.84373 2.88749C6.12186 2.16562 4.99373 2.16562 4.27186 2.88749L2.62186 4.53749C2.24373 4.88124 2.03748 5.36249 2.03748 5.87812C2.03748 6.39374 2.24373 6.87499 2.62186 7.21874L2.69061 7.28749C2.79373 7.39062 2.86248 7.52812 2.79373 7.66562C2.72498 7.80312 2.65623 7.90624 2.62186 8.04374C2.58748 8.18124 2.44998 8.28437 2.27811 8.28437H2.17498C1.65936 8.28437 1.17811 8.49062 0.834356 8.86874C0.490606 9.24687 0.284356 9.72812 0.284356 10.2437V12.4406C0.284356 13.4719 1.11248 14.3 2.14373 14.3H2.24686C2.41873 14.3 2.55623 14.4031 2.59061 14.5406C2.62498 14.6781 2.69373 14.7812 2.72811 14.9187C2.79686 15.0562 2.72811 15.1937 2.62498 15.2969L2.55623 15.3656C2.17811 15.7094 1.97186 16.1906 1.97186 16.7062C1.97186 17.2219 2.17811 17.7031 2.55623 18.0469L4.20623 19.6969C4.92811 20.4187 6.05623 20.4187 6.77811 19.6969L6.84686 19.6281C6.94998 19.525 7.08748 19.4562 7.22498 19.525C7.36248 19.5937 7.46561 19.6625 7.60311 19.6969C7.74061 19.7312 7.84373 19.8687 7.84373 20.0406V20.1437C7.84373 21.175 8.67186 22.0031 9.70311 22.0031H11.9C12.9312 22.0031 13.7594 21.175 13.7594 20.1437V20.0406C13.7594 19.8687 13.8625 19.7312 14 19.6969C14.1375 19.6625 14.2406 19.5937 14.3781 19.525C14.5156 19.4562 14.6531 19.525 14.7562 19.6281L14.825 19.6969C15.5469 20.4187 16.675 20.4187 17.3969 19.6969L19.0469 18.0469C19.425 17.7031 19.6312 17.2219 19.6312 16.7062C19.6312 16.1906 19.425 15.7094 19.0469 15.3656L18.9781 15.2969C18.875 15.1937 18.8062 15.0562 18.875 14.9187C18.9437 14.7812 19.0125 14.6781 19.0469 14.5406C19.0812 14.4031 19.2187 14.3 19.3906 14.3H19.4937C20.525 14.3 21.3531 13.4719 21.3531 12.4406V10.2437C21.3531 9.72812 21.1469 9.24687 20.8656 8.86874ZM19.8062 12.4406C19.8062 12.6125 19.6687 12.75 19.4969 12.75H19.3937C18.5656 12.75 17.8781 13.2656 17.7062 14.0594C17.6719 14.2312 17.6031 14.4031 17.5344 14.5406C16.9844 15.6719 17.2969 17.0781 18.2594 17.8687L18.3281 17.9375C18.4312 18.0406 18.4312 18.2125 18.3281 18.3156L16.6781 19.9656C16.575 20.0687 16.4031 20.0687 16.3 19.9656L16.2312 19.8969C15.4406 18.9344 14.0344 18.6219 12.9031 19.1719C12.7656 19.2406 12.5937 19.3094 12.4219 19.3437C11.6281 19.5156 11.1125 20.2031 11.1125 21.0312V21.1344C11.1125 21.3062 10.975 21.4437 10.8031 21.4437H8.60623C8.43436 21.4437 8.29686 21.3062 8.29686 21.1344V21.0312C8.29686 20.2031 7.78123 19.5156 6.98748 19.3437C6.81561 19.3094 6.64373 19.2406 6.50623 19.1719C5.37498 18.6219 3.96873 18.9344 3.17811 19.8969L3.10936 19.9656C3.00623 20.0687 2.83436 20.0687 2.73123 19.9656L1.08123 18.3156C0.978106 18.2125 0.978106 18.0406 1.08123 17.9375L1.14998 17.8687C2.11248 17.0781 2.42498 15.6719 1.87498 14.5406C1.80623 14.4031 1.73748 14.2312 1.70311 14.0594C1.53123 13.2656 0.843731 12.75 0.0156058 12.75H-0.0874942C-0.259369 12.75 -0.396869 12.6125 -0.396869 12.4406V10.2437C-0.396869 10.0719 -0.259369 9.93437 -0.0874942 9.93437H0.0156058C0.843731 9.93437 1.53123 9.41874 1.70311 8.62499C1.73748 8.45312 1.80623 8.28124 1.87498 8.14374C2.42498 7.01249 2.11248 5.60624 1.14998 4.81562L1.08123 4.74687C0.978106 4.64374 0.978106 4.47187 1.08123 4.36874L2.73123 2.71874C2.83436 2.61562 3.00623 2.61562 3.10936 2.71874L3.17811 2.78749C3.96873 3.74999 5.37498 4.06249 6.50623 3.51249C6.64373 3.44374 6.81561 3.37499 6.98748 3.34062C7.78123 3.16874 8.29686 2.48124 8.29686 1.65312V1.54999C8.29686 1.37812 8.43436 1.24062 8.60623 1.24062H10.8031C10.975 1.24062 11.1125 1.37812 11.1125 1.54999V1.65312C11.1125 2.48124 11.6281 3.16874 12.4219 3.34062C12.5937 3.37499 12.7656 3.44374 12.9031 3.51249C14.0344 4.06249 15.4406 3.74999 16.2312 2.78749L16.3 2.71874C16.4031 2.61562 16.575 2.61562 16.6781 2.71874L18.3281 4.36874C18.4312 4.47187 18.4312 4.64374 18.3281 4.74687L18.2594 4.81562C17.2969 5.60624 16.9844 7.01249 17.5344 8.14374C17.6031 8.28124 17.6719 8.45312 17.7062 8.62499C17.8781 9.41874 18.5656 9.93437 19.3937 9.93437H19.4969C19.6687 9.93437 19.8062 10.0719 19.8062 10.2437V12.4406Z"
                                        fill=""
                                    />
                                    <path
                                        d="M11 6.32812C8.73125 6.32812 6.89062 8.16875 6.89062 10.4375C6.89062 12.7062 8.73125 14.5469 11 14.5469C13.2688 14.5469 15.1094 12.7062 15.1094 10.4375C15.1094 8.16875 13.2688 6.32812 11 6.32812ZM11 13C9.59375 13 8.4375 11.8437 8.4375 10.4375C8.4375 9.03125 9.59375 7.875 11 7.875C12.4062 7.875 13.5625 9.03125 13.5625 10.4375C13.5625 11.8437 12.4062 13 11 13Z"
                                        fill=""
                                    />
                                </svg>
                                Account Settings
                            </a>
                        </li>
                    </ul>
                    <button class="flex items-center gap-3.5 px-6 py-4 text-sm font-medium duration-300 ease-in-out hover:text-primary lg:text-base">
                        <svg class="fill-current" width="22" height="22" viewBox="0 0 22 22" fill="none">
                            <path
                                d="M15.5375 0.618744H11.6531C10.7594 0.618744 10.0031 1.37499 10.0031 2.26874V4.64062C10.0031 5.05312 10.3469 5.39687 10.7594 5.39687C11.1719 5.39687 11.5156 5.05312 11.5156 4.64062V2.23437C11.5156 2.16562 11.5844 2.09687 11.6531 2.09687H15.5375C16.3625 2.09687 17.0156 2.74999 17.0156 3.57499V18.425C17.0156 19.25 16.3625 19.9031 15.5375 19.9031H11.6531C11.5844 19.9031 11.5156 19.8344 11.5156 19.7656V17.3594C11.5156 16.9469 11.1719 16.6031 10.7594 16.6031C10.3469 16.6031 10.0031 16.9469 10.0031 17.3594V19.7312C10.0031 20.625 10.7594 21.3812 11.6531 21.3812H15.5375C17.2219 21.3812 18.5844 20.0187 18.5844 18.3344V3.66562C18.5844 1.98124 17.2219 0.618744 15.5375 0.618744Z"
                                fill=""
                            />
                            <path
                                d="M6.05001 11.7563H12.2031C12.6156 11.7563 12.9594 11.4125 12.9594 11C12.9594 10.5875 12.6156 10.2438 12.2031 10.2438H6.08439L8.21564 8.07813C8.52501 7.76875 8.52501 7.2875 8.21564 6.97812C7.90626 6.66875 7.42501 6.66875 7.11564 6.97812L3.67814 10.4156C3.36876 10.725 3.36876 11.2063 3.67814 11.5156L7.11564 14.9531C7.27001 15.1075 7.49376 15.1844 7.71751 15.1844C7.94126 15.1844 8.16501 15.1075 8.31939 14.9531C8.62876 14.6438 8.62876 14.1625 8.31939 13.8531L6.05001 11.7563Z"
                                fill=""
                            />
                        </svg>
                        Log Out
                    </button>
                </div>
            </div>
        </div>
    </div>
</header>
