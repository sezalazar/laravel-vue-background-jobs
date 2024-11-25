<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});

const isDropdownOpen = ref(false);

function toggleDropdown() {
    isDropdownOpen.value = !isDropdownOpen.value;
}

function closeDropdown() {
    isDropdownOpen.value = false;
}
</script>

<template>
    <Head title="Welcome" />
    <header class="bg-gray-50 text-black/50 dark:bg-black dark:text-white/50 shadow-md">
        <div class="flex items-center justify-between px-6 py-4">
            <!-- Logo -->
            <a href="/">
                <img src="/images/company_logo.jpeg" alt="Logo" class="h-12 w-auto" />
            </a>

            <!-- Navigation -->
            <nav class="flex items-center gap-4">
                <template v-if="$page.props.auth.user">
                    <!-- Dropdown Menu -->
                    <div class="relative">
                        <button
                            @click="toggleDropdown"
                            @blur="closeDropdown"
                            class="flex items-center text-sm font-medium text-gray-700 hover:text-gray-900 focus:outline-none dark:text-gray-300 dark:hover:text-white"
                        >
                            {{ $page.props.auth.user.name }}
                            <svg
                                class="ml-1 h-4 w-4"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M19 9l-7 7-7-7"
                                />
                            </svg>
                        </button>
                        <div
                            v-if="isDropdownOpen"
                            class="absolute right-0 mt-2 w-48 bg-white shadow-lg ring-1 ring-black ring-opacity-5 dark:bg-gray-700"
                        >
                            <Link
                                href="/profile"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-600"
                            >
                                Profile
                            </Link>
                            <form method="POST" action="/logout">
                                <input type="hidden" name="_token" :value="$page.props.csrf_token" />
                                <button
                                    type="submit"
                                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-600"
                                >
                                    Log Out
                                </button>
                            </form>
                        </div>
                    </div>
                </template>
                <template v-else>
                    <Link
                        href="/login"
                        class="text-sm font-medium text-blue-500 hover:underline"
                    >
                        Log In
                    </Link>
                    <Link
                        v-if="canRegister"
                        href="/register"
                        class="text-sm font-medium text-blue-500 hover:underline"
                    >
                        Register
                    </Link>
                </template>
            </nav>
        </div>
    </header>
</template>
