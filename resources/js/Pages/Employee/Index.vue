<script setup>
import { ref, watch, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';

const debounce = (fn, delay = 300) => {
    let timeoutId;
    return (...args) => {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => fn(...args), delay);
    };
};

const props = defineProps({
    employees: Object,
    departments: Array,
    filters: Object
});

const search = ref(props.filters.search || '');
const department_id = ref(props.filters.department_id || '');
const statusFilter = ref(props.filters.status || 'active');
const viewMode = ref('table'); // grid | table

watch([search, department_id, statusFilter], debounce(([newSearch, newDept, newStatus]) => {
    router.get(route('employees.index'), {
        search: newSearch,
        department_id: newDept,
        status: newStatus
    }, { preserveState: true, replace: true });
}, 300));

const palettes = [
    { grad: 'from-indigo-500 to-purple-500', ring: 'ring-indigo-100' },
    { grad: 'from-sky-500 to-cyan-500', ring: 'ring-sky-100' },
    { grad: 'from-emerald-500 to-teal-500', ring: 'ring-emerald-100' },
    { grad: 'from-amber-500 to-orange-500', ring: 'ring-amber-100' },
    { grad: 'from-rose-500 to-pink-500', ring: 'ring-rose-100' },
    { grad: 'from-violet-500 to-fuchsia-500', ring: 'ring-violet-100' },
];
const paletteFor = (id) => palettes[id % palettes.length];
const initial = (emp) => ((emp.first_name || '?').charAt(0) + (emp.last_name || '').charAt(0)).toUpperCase();

const getStatusValue = (emp) => emp.employment_status?.value || emp.employment_status;
const getStatusLabel = (emp) => {
    const v = getStatusValue(emp);
    return { active: 'ทำงานอยู่', probation: 'ทดลองงาน', resigned: 'ลาออก', terminated: 'เลิกจ้าง' }[v] || 'พักงาน';
};
const getStatusClass = (emp) => {
    const v = getStatusValue(emp);
    if (v === 'active') return 'bg-emerald-50 text-emerald-700 border-emerald-200';
    if (v === 'probation') return 'bg-amber-50 text-amber-700 border-amber-200';
    return 'bg-rose-50 text-rose-700 border-rose-200';
};
const getStatusDot = (emp) => {
    const v = getStatusValue(emp);
    if (v === 'active') return 'bg-emerald-500';
    if (v === 'probation') return 'bg-amber-500';
    return 'bg-rose-500';
};

const totalEmployees = computed(() => props.employees?.total || 0);
const activeCount = computed(() => (props.employees?.data || []).filter(e => getStatusValue(e) === 'active').length);
const probationCount = computed(() => (props.employees?.data || []).filter(e => getStatusValue(e) === 'probation').length);
</script>

<template>
    <Head title="จัดการพนักงาน" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap gap-4 justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg shadow-indigo-200/60">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="font-extrabold text-2xl text-slate-800 leading-tight">จัดการพนักงาน</h2>
                        <p class="text-xs text-slate-500 font-medium">ข้อมูลพนักงาน ตำแหน่ง และสถานะการทำงาน</p>
                    </div>
                </div>
                <Link :href="route('employees.create')" class="group inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white rounded-xl px-5 py-2.5 text-sm font-bold shadow-lg shadow-indigo-300/50 hover:shadow-indigo-400/60 transition-all hover:-translate-y-0.5">
                    <svg class="w-5 h-5 transition-transform group-hover:rotate-90" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                    เพิ่มพนักงาน
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <!-- Stats -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="relative bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition overflow-hidden group">
                        <div class="absolute -right-6 -top-6 w-24 h-24 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-full opacity-60 group-hover:scale-110 transition-transform"></div>
                        <div class="relative">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center shadow-md shadow-indigo-200/60 mb-3">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                            </div>
                            <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">พนักงานทั้งหมด</div>
                            <div class="text-3xl font-black text-slate-800 mt-1">{{ totalEmployees }}</div>
                        </div>
                    </div>
                    <div class="relative bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition overflow-hidden group">
                        <div class="absolute -right-6 -top-6 w-24 h-24 bg-gradient-to-br from-emerald-100 to-teal-100 rounded-full opacity-60 group-hover:scale-110 transition-transform"></div>
                        <div class="relative">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-500 flex items-center justify-center shadow-md shadow-emerald-200/60 mb-3">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            </div>
                            <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">ทำงานอยู่</div>
                            <div class="text-3xl font-black text-slate-800 mt-1">{{ activeCount }}</div>
                        </div>
                    </div>
                    <div class="relative bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition overflow-hidden group">
                        <div class="absolute -right-6 -top-6 w-24 h-24 bg-gradient-to-br from-amber-100 to-orange-100 rounded-full opacity-60 group-hover:scale-110 transition-transform"></div>
                        <div class="relative">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-500 to-orange-500 flex items-center justify-center shadow-md shadow-amber-200/60 mb-3">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                            </div>
                            <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">ทดลองงาน</div>
                            <div class="text-3xl font-black text-slate-800 mt-1">{{ probationCount }}</div>
                        </div>
                    </div>
                    <div class="relative bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition overflow-hidden group">
                        <div class="absolute -right-6 -top-6 w-24 h-24 bg-gradient-to-br from-sky-100 to-blue-100 rounded-full opacity-60 group-hover:scale-110 transition-transform"></div>
                        <div class="relative">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-sky-500 to-blue-500 flex items-center justify-center shadow-md shadow-sky-200/60 mb-3">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                            </div>
                            <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">แผนก</div>
                            <div class="text-3xl font-black text-slate-800 mt-1">{{ departments?.length || 0 }}</div>
                        </div>
                    </div>
                </div>

                <!-- Toolbar -->
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 flex flex-wrap gap-4 items-center justify-between">
                    <div class="flex flex-wrap gap-3 flex-1">
                        <div class="relative flex-1 min-w-[220px]">
                            <svg class="w-5 h-5 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                            <input v-model="search" type="text" placeholder="ค้นหาชื่อ, รหัสพนักงาน..." class="w-full pl-11 pr-4 py-2.5 rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm font-medium bg-slate-50/60" />
                        </div>
                        <div class="relative min-w-[180px]">
                            <svg class="w-5 h-5 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5" /></svg>
                            <select v-model="department_id" class="w-full pl-11 pr-4 py-2.5 rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm font-medium bg-slate-50/60">
                                <option value="">แสดงทุกแผนก</option>
                                <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <div class="inline-flex bg-slate-100 rounded-xl p-1 text-xs font-bold">
                            <button @click="statusFilter = 'active'" :class="statusFilter === 'active' ? 'bg-white text-emerald-700 shadow' : 'text-slate-500 hover:text-slate-700'" class="px-3 py-2 rounded-lg transition inline-flex items-center gap-1.5 min-w-[90px] justify-center">
                                ทำงานอยู่
                            </button>
                            <button @click="statusFilter = 'inactive'" :class="statusFilter === 'inactive' ? 'bg-white text-rose-700 shadow' : 'text-slate-500 hover:text-slate-700'" class="px-3 py-2 rounded-lg transition inline-flex items-center gap-1.5 min-w-[90px] justify-center">
                                พ้นสภาพ
                            </button>
                        </div>
                        <div class="hidden sm:block h-8 w-px bg-slate-200"></div>
                        <div class="inline-flex bg-slate-100 rounded-xl p-1 text-xs font-bold">
                            <button @click="viewMode = 'grid'" :class="viewMode === 'grid' ? 'bg-white text-indigo-700 shadow' : 'text-slate-500 hover:text-slate-700'" class="px-3 py-2 rounded-lg transition inline-flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                                การ์ด
                            </button>
                            <button @click="viewMode = 'table'" :class="viewMode === 'table' ? 'bg-white text-indigo-700 shadow' : 'text-slate-500 hover:text-slate-700'" class="px-3 py-2 rounded-lg transition inline-flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>
                                ตาราง
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Card View -->
                <div v-if="viewMode === 'grid' && employees.data.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                    <div v-for="emp in employees.data" :key="emp.id"
                         class="group relative bg-white rounded-2xl border border-slate-100 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                        <!-- Cover gradient -->
                        <div :class="['h-20 bg-gradient-to-br relative overflow-hidden', paletteFor(emp.id).grad]">
                            <div class="absolute -top-4 -right-4 w-20 h-20 bg-white/10 rounded-full"></div>
                            <div class="absolute -bottom-6 -left-4 w-24 h-24 bg-white/10 rounded-full"></div>
                        </div>
                        <!-- Avatar -->
                        <div class="px-5 -mt-10 relative">
                            <div :class="['w-20 h-20 rounded-2xl bg-gradient-to-br flex items-center justify-center text-white font-black text-2xl shadow-xl ring-4 ring-white overflow-hidden', paletteFor(emp.id).grad]">
                                <img v-if="emp.profile_photo" :src="'/storage/' + emp.profile_photo" class="w-full h-full object-cover">
                                <template v-else>{{ initial(emp) }}</template>
                            </div>
                        </div>
                        <!-- Body -->
                        <div class="p-5 pt-3">
                            <h3 class="font-extrabold text-slate-800 text-base leading-tight truncate">{{ emp.first_name }} {{ emp.last_name }}</h3>
                            <p class="text-xs font-mono text-slate-400 mt-0.5 truncate">{{ emp.employee_code }}</p>

                            <div class="mt-3 space-y-1.5">
                                <div v-if="emp.position" class="flex items-center gap-2 text-xs text-slate-600">
                                    <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                    <span class="font-semibold truncate">{{ emp.position.name }}</span>
                                </div>
                                <div v-if="emp.department" class="flex items-center gap-2 text-xs text-slate-600">
                                    <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1" /></svg>
                                    <span class="font-semibold truncate">{{ emp.department.name }}</span>
                                </div>
                            </div>

                            <div class="mt-4 pt-4 border-t border-dashed border-slate-100 flex items-center justify-between">
                                <span :class="['inline-flex items-center gap-1.5 text-[10px] font-bold px-2.5 py-1 rounded-full border', getStatusClass(emp)]">
                                    <span :class="['w-1.5 h-1.5 rounded-full', getStatusDot(emp)]"></span>
                                    {{ getStatusLabel(emp) }}
                                </span>
                                <Link :href="route('employees.edit', emp.id)" class="w-9 h-9 rounded-xl bg-slate-50 hover:bg-indigo-50 text-slate-500 hover:text-indigo-600 flex items-center justify-center transition" title="แก้ไข">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table View -->
                <div v-else-if="viewMode === 'table' && employees.data.length > 0" class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">พนักงาน</th>
                                <th class="px-4 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">รหัส</th>
                                <th class="px-4 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">ตำแหน่ง / แผนก</th>
                                <th class="px-4 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">สถานะ</th>
                                <th class="px-4 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="emp in employees.data" :key="emp.id" class="hover:bg-slate-50/50 transition">
                                <td class="px-6 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div :class="['w-10 h-10 rounded-xl bg-gradient-to-br flex items-center justify-center text-white font-black text-sm shrink-0 shadow-md overflow-hidden', paletteFor(emp.id).grad]">
                                            <img v-if="emp.profile_photo" :src="'/storage/' + emp.profile_photo" class="w-full h-full object-cover">
                                            <template v-else>{{ initial(emp) }}</template>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-bold text-slate-800 text-sm truncate">{{ emp.first_name }} {{ emp.last_name }}</div>
                                            <div class="text-xs text-slate-400 truncate">{{ emp.user?.email || '-' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 font-mono text-sm text-slate-600">{{ emp.employee_code }}</td>
                                <td class="px-4 py-3.5">
                                    <div class="font-semibold text-sm text-slate-700">{{ emp.position?.name || '—' }}</div>
                                    <div class="text-xs text-slate-400">{{ emp.department?.name || '—' }}</div>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span :class="['inline-flex items-center gap-1.5 text-[10px] font-bold px-2.5 py-1 rounded-full border', getStatusClass(emp)]">
                                        <span :class="['w-1.5 h-1.5 rounded-full', getStatusDot(emp)]"></span>
                                        {{ getStatusLabel(emp) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-right">
                                    <Link :href="route('employees.edit', emp.id)" class="inline-flex items-center gap-1 text-sm font-bold text-indigo-600 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-lg transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                        แก้ไข
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty -->
                <div v-else class="bg-white rounded-2xl border border-dashed border-slate-200 py-16 text-center">
                    <div class="inline-flex w-20 h-20 rounded-3xl bg-gradient-to-br from-slate-100 to-slate-200 items-center justify-center mb-4">
                        <svg class="w-10 h-10 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    </div>
                    <h3 class="font-extrabold text-slate-700 text-lg">ไม่พบข้อมูลพนักงาน</h3>
                    <p class="text-sm text-slate-400 font-medium mt-1">ลองเปลี่ยนคำค้นหาหรือตัวกรอง</p>
                </div>

                <!-- Pagination -->
                <div v-if="employees.data.length > 0" class="flex justify-end">
                    <Pagination :links="employees.links" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
