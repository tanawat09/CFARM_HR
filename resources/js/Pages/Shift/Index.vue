<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    shifts: Object
});
</script>

<template>
    <Head title="จัดการกะทำงาน" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h2 class="font-bold text-2xl text-slate-800 leading-tight tracking-tight">⏰ จัดการกะทำงาน (Shifts)</h2>
                    <p class="text-slate-500 text-sm mt-1">กำหนดช่วงเวลาเข้า-ออกงานและเงื่อนไขการทำงาน</p>
                </div>
                <Link :href="route('shifts.create')" class="inline-flex items-center gap-2 bg-indigo-600 border border-transparent rounded-xl px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 active:bg-indigo-800 transition shadow-md hover:shadow-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    เพิ่มกะทำงาน
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="glass sm:rounded-3xl overflow-hidden shadow-xl border border-white/60 p-8 relative">
                    <div class="absolute top-0 right-0 w-64 h-64 bg-indigo-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 pointer-events-none"></div>

                    <div class="overflow-x-auto relative z-10 bg-white/60 rounded-2xl border border-slate-100 shadow-sm">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-indigo-50/50 text-indigo-900 border-b border-indigo-100/50">
                                    <th class="p-4 font-bold rounded-tl-2xl">ข้อมูลกะ</th>
                                    <th class="p-4 font-bold text-center">เวลาทำงาน</th>
                                    <th class="p-4 font-bold text-center">เงื่อนไข (สาย/พัก/OT)</th>
                                    <th class="p-4 font-bold text-center">วันทำงาน</th>
                                    <th class="p-4 font-bold text-center">สถานะ</th>
                                    <th class="p-4 font-bold text-center rounded-tr-2xl">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm">
                                <tr v-for="shift in shifts.data" :key="shift.id" class="hover:bg-indigo-50/30 transition duration-150">
                                    <td class="p-4">
                                        <p class="font-bold text-slate-800 text-base">{{ shift.name }}</p>
                                        <p class="text-xs text-slate-500 font-mono mt-0.5">รหัส: {{ shift.code }}</p>
                                    </td>
                                    <td class="p-4 text-center">
                                        <div class="inline-flex items-center gap-2 bg-slate-100 px-3 py-1.5 rounded-lg border border-slate-200 shadow-sm">
                                            <span class="font-mono font-bold text-emerald-600">{{ shift.start_time.substring(0, 5) }}</span>
                                            <span class="text-slate-400 text-xs">-</span>
                                            <span class="font-mono font-bold text-sky-600">{{ shift.end_time.substring(0, 5) }}</span>
                                        </div>
                                    </td>
                                    <td class="p-4 text-center">
                                        <div class="flex flex-col gap-1 items-center">
                                            <span class="text-xs bg-amber-50 text-amber-700 px-2 py-0.5 rounded border border-amber-200 shadow-sm" title="อนุโลมสาย (นาที)">สาย > {{ shift.late_after_minutes }} นาที</span>
                                            <span class="text-xs bg-purple-50 text-purple-700 px-2 py-0.5 rounded border border-purple-200 shadow-sm" title="ระยะเวลาพัก (นาที)">พัก {{ shift.break_duration_minutes }} นาที</span>
                                        </div>
                                    </td>
                                    <td class="p-4 text-center">
                                        <span class="text-slate-600 font-medium">{{ shift.working_days || 'ไม่ระบุ' }}</span>
                                    </td>
                                    <td class="p-4 text-center">
                                        <span class="px-2.5 py-1 text-xs font-bold rounded-full border shadow-sm"
                                            :class="shift.is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-50 text-slate-500 border-slate-200'">
                                            {{ shift.is_active ? 'เปิดใช้งาน' : 'ปิดใช้งาน' }}
                                        </span>
                                    </td>
                                    <td class="p-4 text-center">
                                        <Link :href="route('shifts.edit', shift.id)" class="text-indigo-600 hover:text-indigo-900 font-semibold p-2 hover:bg-indigo-50 rounded-lg transition inline-flex items-center gap-1 shadow-sm border border-transparent hover:border-indigo-100">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            แก้ไข
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-if="shifts.data.length === 0">
                                    <td colspan="6" class="p-8 text-center text-slate-500 bg-slate-50/50 rounded-b-2xl">
                                        <div class="text-3xl mb-2">⏰</div>
                                        ยังไม่มีข้อมูลกะทำงาน
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
