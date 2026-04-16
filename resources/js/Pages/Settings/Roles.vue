<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineProps({
    roles: Array,
});

const permissions = [
    {
        group: 'การจัดการระบบ',
        icon: '⚙️',
        items: [
            { name: 'เข้าถึงหน้าตั้งค่าระบบ', admin: true, hr: true, md: false, deputy: false, manager: false, supervisor: false, employee: false },
            { name: 'จัดการนโยบายการลา', admin: true, hr: true, md: false, deputy: false, manager: false, supervisor: false, employee: false },
            { name: 'จัดการวันหยุดบริษัท', admin: true, hr: true, md: false, deputy: false, manager: false, supervisor: false, employee: false },
            { name: 'ตั้งค่า LINE Messaging', admin: true, hr: true, md: false, deputy: false, manager: false, supervisor: false, employee: false },
            { name: 'จัดการสิทธิ์การใช้งาน', admin: true, hr: false, md: false, deputy: false, manager: false, supervisor: false, employee: false },
        ],
    },
    {
        group: 'การจัดการบุคลากร',
        icon: '👥',
        items: [
            { name: 'เพิ่ม/แก้ไข/ลบ พนักงาน', admin: true, hr: true, md: false, deputy: false, manager: false, supervisor: false, employee: false },
            { name: 'จัดการแผนก', admin: true, hr: true, md: false, deputy: false, manager: false, supervisor: false, employee: false },
            { name: 'จัดการตำแหน่ง', admin: true, hr: true, md: false, deputy: false, manager: false, supervisor: false, employee: false },
            { name: 'จัดการกะทำงาน', admin: true, hr: true, md: false, deputy: false, manager: false, supervisor: false, employee: false },
            { name: 'จัดการสาขา', admin: true, hr: true, md: false, deputy: false, manager: false, supervisor: false, employee: false },
        ],
    },
    {
        group: 'การอนุมัติ',
        icon: '✅',
        items: [
            { name: 'อนุมัติ/ไม่อนุมัติ การลา', admin: true, hr: true, md: true, deputy: true, manager: true, supervisor: true, employee: false },
            { name: 'อนุมัติผ่าน LINE', admin: true, hr: true, md: true, deputy: true, manager: true, supervisor: true, employee: false },
            { name: 'ดูรายการรออนุมัติ', admin: true, hr: true, md: true, deputy: true, manager: true, supervisor: true, employee: false },
        ],
    },
    {
        group: 'การลาหยุด',
        icon: '🗓️',
        items: [
            { name: 'สร้างคำขอลา', admin: true, hr: true, md: true, deputy: true, manager: true, supervisor: true, employee: true },
            { name: 'ดูประวัติการลาตัวเอง', admin: true, hr: true, md: true, deputy: true, manager: true, supervisor: true, employee: true },
        ],
    },
    {
        group: 'การลงเวลา',
        icon: '🕒',
        items: [
            { name: 'ลงเวลาเข้า-ออกงาน (GPS)', admin: true, hr: true, md: true, deputy: true, manager: true, supervisor: true, employee: true },
            { name: 'ดูประวัติการลงเวลาตัวเอง', admin: true, hr: true, md: true, deputy: true, manager: true, supervisor: true, employee: true },
        ],
    },
    {
        group: 'รายงาน',
        icon: '📊',
        items: [
            { name: 'ดูรายงานสรุป', admin: true, hr: true, md: false, deputy: false, manager: false, supervisor: false, employee: false },
            { name: 'ส่งออกรายงาน CSV', admin: true, hr: true, md: false, deputy: false, manager: false, supervisor: false, employee: false },
        ],
    },
];

const roleColumns = [
    { key: 'admin', label: 'Admin', color: 'bg-red-500' },
    { key: 'hr', label: 'HR', color: 'bg-purple-500' },
    { key: 'md', label: 'กรรมการผู้จัดการ', color: 'bg-amber-500' },
    { key: 'deputy', label: 'รองกรรมการ', color: 'bg-orange-500' },
    { key: 'manager', label: 'ผู้จัดการ', color: 'bg-blue-500' },
    { key: 'supervisor', label: 'หัวหน้างาน', color: 'bg-teal-500' },
    { key: 'employee', label: 'พนักงาน', color: 'bg-slate-400' },
];
</script>

<template>
    <Head title="สิทธิ์การใช้งาน" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="route('settings.index')" class="p-2 rounded-xl hover:bg-slate-100 transition-colors text-slate-400 hover:text-slate-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    </Link>
                    <span class="p-3 bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl shadow-lg shadow-amber-200">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </span>
                    <div>
                        <h2 class="font-bold text-2xl text-slate-800 leading-tight">สิทธิ์การใช้งาน</h2>
                        <p class="text-sm text-slate-500 mt-0.5">ตารางแสดงสิทธิ์ของแต่ละบทบาทในระบบ</p>
                    </div>
                </div>
            </div>
        </template>

        <div class="py-10">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <!-- Role Legend -->
                <div class="flex flex-wrap gap-3 mb-8">
                    <div v-for="col in roleColumns" :key="col.key" class="flex items-center gap-2 bg-white/70 backdrop-blur-sm px-4 py-2 rounded-xl border border-slate-200/60 shadow-sm">
                        <span class="w-3 h-3 rounded-full" :class="col.color"></span>
                        <span class="text-sm font-semibold text-slate-700">{{ col.label }}</span>
                    </div>
                </div>

                <!-- Permissions Table -->
                <div class="bg-white/80 backdrop-blur-lg overflow-hidden shadow-xl rounded-3xl border border-white/50">
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <!-- Header -->
                            <thead>
                                <tr class="bg-gradient-to-r from-slate-50 to-slate-100/50">
                                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider sticky left-0 bg-slate-50/90 backdrop-blur-sm z-10 min-w-[220px]">
                                        สิทธิ์การทำงาน
                                    </th>
                                    <th v-for="col in roleColumns" :key="col.key" class="px-3 py-4 text-center min-w-[100px]">
                                        <div class="flex flex-col items-center gap-1.5">
                                            <span class="w-3 h-3 rounded-full" :class="col.color"></span>
                                            <span class="text-[11px] font-bold text-slate-600 uppercase tracking-wide leading-tight">{{ col.label }}</span>
                                        </div>
                                    </th>
                                </tr>
                            </thead>

                            <!-- Body -->
                            <tbody>
                                <template v-for="(section, si) in permissions" :key="si">
                                    <!-- Section Header -->
                                    <tr class="bg-slate-50/70">
                                        <td :colspan="roleColumns.length + 1" class="px-6 py-3">
                                            <div class="flex items-center gap-2">
                                                <span class="text-lg">{{ section.icon }}</span>
                                                <span class="text-sm font-bold text-slate-700 tracking-wide">{{ section.group }}</span>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Permission Rows -->
                                    <tr v-for="(perm, pi) in section.items" :key="`${si}-${pi}`" class="border-b border-slate-100/60 hover:bg-slate-50/40 transition-colors">
                                        <td class="px-6 py-3.5 text-sm text-slate-700 font-medium sticky left-0 bg-white/90 backdrop-blur-sm z-10">
                                            {{ perm.name }}
                                        </td>
                                        <td v-for="col in roleColumns" :key="col.key" class="px-3 py-3.5 text-center">
                                            <span v-if="perm[col.key]" class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-emerald-100 text-emerald-600">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                            </span>
                                            <span v-else class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-slate-100 text-slate-300">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            </span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Info Note -->
                <div class="mt-6 p-5 bg-amber-50/60 backdrop-blur-sm border border-amber-200/60 rounded-2xl">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-amber-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div class="text-sm text-amber-800">
                            <p class="font-semibold mb-1">หมายเหตุ</p>
                            <ul class="list-disc list-inside space-y-1 text-amber-700">
                                <li><strong>Admin</strong> มีสิทธิ์สูงสุดในการจัดการระบบทั้งหมด</li>
                                <li><strong>HR</strong> สามารถจัดการข้อมูลพนักงาน ตั้งค่าระบบ และอนุมัติการลาได้</li>
                                <li><strong>กรรมการผู้จัดการ / รองกรรมการ / ผู้จัดการ / หัวหน้างาน</strong> สามารถอนุมัติการลาของผู้ใต้บังคับบัญชาได้</li>
                                <li><strong>พนักงาน</strong> สามารถลงเวลาทำงาน สร้างคำขอลา และดูประวัติของตัวเองได้</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
