<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';

// Custom debounce function to replace lodash
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

watch([search, department_id], debounce(([newSearch, newDept]) => {
    router.get(route('employees.index'), {
        search: newSearch,
        department_id: newDept
    }, { preserveState: true, replace: true });
}, 300));
</script>

<template>
    <Head title="จัดการพนักงาน" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-bold text-2xl text-slate-800 leading-tight tracking-tight">👤 จัดการพนักงาน</h2>
                <Link :href="route('employees.create')" class="inline-flex items-center gap-2 bg-indigo-600 border border-transparent rounded-xl px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 active:bg-indigo-800 transition shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    เพิ่มพนักงาน
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Premium Glass Card -->
                <div class="glass sm:rounded-3xl overflow-hidden shadow-xl border border-white/60 p-8 relative">
                    <!-- Background blobs -->
                    <div class="absolute top-0 right-0 w-64 h-64 bg-indigo-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 pointer-events-none"></div>

                    <!-- Filters -->
                    <div class="flex flex-col sm:flex-row gap-4 justify-between items-center mb-8 relative z-10 w-full">
                        <div class="w-full sm:w-1/3 relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                            </div>
                            <input v-model="search" type="text" placeholder="ค้นหาชื่อ, รหัสพนักงาน..." class="pl-10 block w-full rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-white/70 backdrop-blur transition hover:bg-white" />
                        </div>
                        <div class="w-full sm:w-1/4">
                            <select v-model="department_id" class="block w-full rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-white/70 backdrop-blur transition hover:bg-white">
                                <option value="">แสดงทุกแผนก</option>
                                <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                                    {{ dept.name }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Data Table -->
                    <div class="overflow-x-auto relative z-10 bg-white/60 rounded-2xl border border-slate-100 shadow-sm">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-indigo-50/50 text-indigo-900 border-b border-indigo-100/50">
                                    <th class="p-4 font-bold rounded-tl-2xl">ข้อมูลพนักงาน</th>
                                    <th class="p-4 font-bold">รหัส</th>
                                    <th class="p-4 font-bold">ตำแหน่ง/แผนก</th>
                                    <th class="p-4 font-bold">สถานะ</th>
                                    <th class="p-4 font-bold text-center rounded-tr-2xl">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm">
                                <tr v-for="emp in employees.data" :key="emp.id" class="hover:bg-indigo-50/30 transition duration-150">
                                    <td class="p-4 flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-full bg-slate-200 border-2 border-white shadow-sm overflow-hidden shrink-0">
                                            <svg v-if="!emp.profile_photo" class="w-full h-full text-slate-400 p-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" /></svg>
                                            <img v-else :src="'/storage/' + emp.profile_photo" class="w-full h-full object-cover">
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-800">{{ emp.first_name }} {{ emp.last_name }}</p>
                                            <p class="text-xs text-slate-500">{{ emp.user?.email || '-' }}</p>
                                        </div>
                                    </td>
                                    <td class="p-4 font-mono text-slate-600">{{ emp.employee_code }}</td>
                                    <td class="p-4">
                                        <p class="font-semibold text-slate-700">{{ emp.position?.name }}</p>
                                        <p class="text-xs text-slate-500">{{ emp.department?.name }}</p>
                                    </td>
                                    <td class="p-4">
                                        <span class="px-2.5 py-1 text-xs font-bold rounded-full border shadow-sm"
                                            :class="{
                                                'bg-emerald-50 text-emerald-700 border-emerald-200': (emp.employment_status?.value || emp.employment_status) === 'active',
                                                'bg-amber-50 text-amber-700 border-amber-200': (emp.employment_status?.value || emp.employment_status) === 'probation',
                                                'bg-rose-50 text-rose-700 border-rose-200': !['active','probation'].includes(emp.employment_status?.value || emp.employment_status)
                                            }">
                                            {{ (emp.employment_status?.value || emp.employment_status) === 'active' ? 'ทำงานอยู่' : (emp.employment_status?.value || emp.employment_status) === 'probation' ? 'ทดลองงาน' : (emp.employment_status?.value || emp.employment_status) === 'resigned' ? 'ลาออก' : (emp.employment_status?.value || emp.employment_status) === 'terminated' ? 'เลิกจ้าง' : 'พักงาน' }}
                                        </span>
                                    </td>
                                    <td class="p-4 text-center">
                                        <Link :href="route('employees.edit', emp.id)" class="text-indigo-600 hover:text-indigo-900 font-semibold p-2 hover:bg-indigo-50 rounded-lg transition inline-flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            แก้ไข
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-if="employees.data.length === 0">
                                    <td colspan="5" class="p-8 text-center text-slate-500 bg-slate-50/50">
                                        ไม่พบข้อมูลพนักงาน
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="mt-6 flex justify-end relative z-10">
                        <Pagination :links="employees.links" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
