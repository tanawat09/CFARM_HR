<script setup>
import { ref, computed } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { Link, usePage } from '@inertiajs/vue3';

const showingSidebar = ref(false);
const page = usePage();

const userRole = computed(() => {
    const role = page.props.auth.user.role;
    return typeof role === 'object' ? role.value : role;
});

const isAdminOrHR = computed(() => userRole.value === 'admin' || userRole.value === 'hr');

const isSupervisorOrAbove = computed(() => ['admin', 'hr', 'supervisor'].includes(userRole.value));

const menuItems = computed(() => {
    const items = [
        { name: 'หน้าหลัก', url: route('dashboard'), active: route().current('dashboard'), icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' },
        { name: 'การลาหยุด', url: route('leave.index'), active: route().current('leave.index') || route().current('leave.create'), icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z' },
    ];

    if (isSupervisorOrAbove.value) {
        items.push({ name: 'อนุมัติการลา', url: route('leave.approvals'), active: route().current('leave.approvals'), icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' });
    }
    
    if (isAdminOrHR.value) {
        items.push({ name: 'พนักงาน', url: route('employees.index'), active: route().current('employees.*'), icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z', admin: true });
        items.push({ name: 'สาขา', url: route('worksites.index'), active: route().current('worksites.*'), icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4', admin: true });
        items.push({ name: 'แผนก', url: route('departments.index'), active: route().current('departments.*'), icon: 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z', admin: true });
        items.push({ name: 'ตำแหน่ง', url: route('positions.index'), active: route().current('positions.*'), icon: 'M21 13.255A23.193 23.193 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', admin: true });
        items.push({ name: 'กะทำงาน', url: route('shifts.index'), active: route().current('shifts.*'), icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', admin: true });
        items.push({ name: 'รายงาน', url: route('reports.index'), active: route().current('reports.*'), icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', admin: true });
        items.push({ name: 'ตั้งค่าการลา', url: route('settings.leave'), active: route().current('settings.leave'), icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z', admin: true });
        items.push({ name: 'ตั้งค่า LINE', url: route('settings.line'), active: route().current('settings.line'), icon: 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z', admin: true });
        items.push({ name: 'ตั้งค่าวันหยุด', url: route('settings.holidays'), active: route().current('settings.holidays'), icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', admin: true });
    }
    return items;
});
</script>

<template>
    <div class="min-h-screen bg-slate-50 font-sans flex font-sans">
        
        <!-- Mobile Sidebar Backdrop -->
        <div v-show="showingSidebar" class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-sm lg:hidden" @click="showingSidebar = false"></div>

        <!-- Sidebar Navigation -->
        <aside :class="[showingSidebar ? 'translate-x-0' : '-translate-x-full lg:translate-x-0']" class="fixed inset-y-0 left-0 z-50 w-72 bg-gradient-to-b from-emerald-950 via-emerald-900 to-emerald-950 text-white transition-transform duration-300 ease-in-out lg:static lg:block shadow-2xl flex flex-col pt-0">
            <!-- App Logo Area -->
            <div class="flex items-center justify-center p-6 border-b border-white/10 relative overflow-hidden shrink-0">
                <div class="absolute -top-10 -right-10 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
                <Link :href="route('dashboard')" class="flex items-center gap-3 relative z-10 w-full">
                    <div class="bg-white p-2 rounded-xl shadow-lg">
                        <ApplicationLogo class="w-8 h-8 flex-shrink-0 text-emerald-700 fill-current" />
                    </div>
                    <span class="font-extrabold text-xl tracking-wide">CFARM HR</span>
                </Link>
                <button @click="showingSidebar = false" class="lg:hidden absolute right-4 top-1/2 -translate-y-1/2 text-white/50 hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- Scrollable Navigation Area -->
            <div class="flex-1 overflow-y-auto overflow-x-hidden p-4 space-y-2 custom-scrollbar">
                <div v-for="(item, index) in menuItems" :key="index">
                    <!-- Section Divider for Admin -->
                    <div v-if="item.admin && index > 0 && !menuItems[index-1].admin" class="my-6 px-3">
                        <p class="text-xs font-bold text-emerald-400 uppercase tracking-wider">🛠️ การจัดการระบบ</p>
                    </div>

                    <Link :href="item.url" :class="[item.active ? 'bg-white/10 text-white shadow-sm ring-1 ring-white/20' : 'text-emerald-100/70 hover:bg-emerald-400/5 hover:text-white', 'flex items-center gap-4 px-4 py-3.5 rounded-xl transition-all duration-200 group relative mb-2 overflow-hidden']">
                        <!-- Active indicator bar -->
                        <div v-if="item.active" class="absolute left-0 top-0 bottom-0 w-1 bg-teal-400 rounded-r-lg"></div>
                        <div v-if="item.active" class="absolute inset-0 bg-gradient-to-r from-teal-400/20 to-transparent"></div>
                        
                        <div :class="[item.active ? 'text-teal-300 scale-110' : 'text-emerald-400 group-hover:text-teal-300 group-hover:scale-110', 'transition-all duration-300 relative z-10 shrink-0']">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon"></path>
                            </svg>
                        </div>
                        <span class="font-bold text-sm tracking-wide relative z-10">{{ item.name }}</span>
                    </Link>
                </div>
            </div>
            
            <!-- User Profile Area at Bottom -->
            <div class="shrink-0 p-4 border-t border-white/10 bg-black/20">
                <Dropdown align="top" width="48" placement="top-start">
                    <template #trigger>
                        <button class="flex items-center gap-3 w-full p-2 rounded-xl hover:bg-white/5 transition-colors text-left focus:outline-none">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-emerald-400 to-teal-600 flex items-center justify-center text-white font-bold shadow-inner shrink-0">
                                {{ ($page.props.auth.user.username || $page.props.auth.user.email).charAt(0).toUpperCase() }}
                            </div>
                            <div class="flex-1 overflow-hidden">
                                <div class="font-bold text-sm text-white truncate">
                                    {{ $page.props.auth.user.employee ? ($page.props.auth.user.employee.first_name + ' ' + $page.props.auth.user.employee.last_name) : ($page.props.auth.user.username || $page.props.auth.user.email) }}
                                </div>
                                <div class="text-xs text-emerald-300 truncate">{{ $page.props.auth.user.role?.name || $page.props.auth.user.role }}</div>
                            </div>
                            <svg class="w-5 h-5 text-emerald-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                        </button>
                    </template>
                    <template #content>
                        <DropdownLink :href="route('profile.edit')">โปรไฟล์ (Profile)</DropdownLink>
                        <DropdownLink :href="route('logout')" method="post" as="button">ออกจากระบบ (Log Out)</DropdownLink>
                    </template>
                </Dropdown>
            </div>
        </aside>

        <!-- Main Content Wrapper -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Top Mobile Bar & Optional Main Topbar -->
            <nav class="sticky top-0 z-30 lg:hidden border-b border-slate-200/50 bg-white/70 backdrop-blur-lg shadow-sm">
                <div class="px-4 sm:px-6">
                    <div class="flex h-16 justify-between items-center">
                        <div class="flex shrink-0 items-center gap-3">
                            <button @click="showingSidebar = true" class="p-2 rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-700 transition focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                            </button>
                            <Link :href="route('dashboard')">
                                <ApplicationLogo class="block h-8 w-auto text-emerald-600 fill-current" />
                            </Link>
                        </div>
                        
                        <!-- Actions on mobile topbar -->
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-bold text-slate-700">
                                {{ $page.props.auth.user.employee ? ($page.props.auth.user.employee.first_name + ' ' + $page.props.auth.user.employee.last_name) : $page.props.auth.user.username }}
                            </span>
                            <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-emerald-400 to-teal-600 flex items-center justify-center text-white text-xs font-bold shadow-sm">
                                {{ ($page.props.auth.user.username || $page.props.auth.user.email).charAt(0).toUpperCase() }}
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header class="bg-transparent relative z-20" v-if="$slots.header">
                <div class="px-4 py-8 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Main Page Content -->
            <main class="flex-1 relative z-10 pb-12 overflow-y-auto">
                <slot />
            </main>
        </div>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 20px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.2);
}
</style>
