<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    shift: Object
});

const form = useForm({
    name: props.shift.name,
    code: props.shift.code,
    start_time: props.shift.start_time,
    end_time: props.shift.end_time,
    late_after_minutes: props.shift.late_after_minutes,
    early_leave_before_minutes: props.shift.early_leave_before_minutes,
    ot_after_minutes: props.shift.ot_after_minutes,
    break_duration_minutes: props.shift.break_duration_minutes,
    working_days: props.shift.working_days,
    is_active: props.shift.is_active
});

const submit = () => {
    form.put(route('shifts.update', props.shift.id));
};
</script>

<template>
    <Head title="แก้ไขกะทำงาน" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h2 class="font-bold text-2xl text-slate-800 leading-tight tracking-tight flex items-center gap-2">
                        <Link :href="route('shifts.index')" class="text-indigo-600 hover:text-indigo-800 transition">⏰ จัดการกะทำงาน</Link>
                        <span class="text-slate-400 text-lg">/</span>
                        <span>แก้ไขกะ - {{ shift.name }}</span>
                    </h2>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden">
                    <div class="border-b border-slate-100 bg-slate-50/50 px-8 py-6">
                        <h3 class="font-bold text-lg text-slate-800">📝 ข้อมูลกะทำงานพื้นฐาน</h3>
                        <p class="text-xs text-slate-500 mt-1">อัปเดตรายละเอียดและเงื่อนไขเวลาของกะทำงานนี้</p>
                    </div>

                    <form @submit.prevent="submit" class="p-8 space-y-8">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Name -->
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">ชื่อกะทำงาน (Name) <span class="text-rose-500">*</span></label>
                                <input v-model="form.name" type="text" class="block w-full rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-slate-50/50" placeholder="เช่น กะเช้า, กะดึก">
                                <InputError class="mt-2" :message="form.errors.name" />
                            </div>

                            <!-- Code -->
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">รหัสกะ (Code) <span class="text-rose-500">*</span></label>
                                <input v-model="form.code" type="text" class="block w-full rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-slate-50/50 font-mono uppercase" placeholder="เช่น SHIFT_M">
                                <InputError class="mt-2" :message="form.errors.code" />
                            </div>

                            <!-- Start Time -->
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">เวลาเข้างาน <span class="text-rose-500">*</span></label>
                                <input v-model="form.start_time" type="time" step="1" class="block w-full rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-slate-50/50 font-mono">
                                <InputError class="mt-2" :message="form.errors.start_time" />
                            </div>

                            <!-- End Time -->
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">เวลาออกงาน <span class="text-rose-500">*</span></label>
                                <input v-model="form.end_time" type="time" step="1" class="block w-full rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-slate-50/50 font-mono">
                                <InputError class="mt-2" :message="form.errors.end_time" />
                            </div>

                            <!-- Late After -->
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">คิดสายหลังจาก (นาที) <span class="text-rose-500">*</span></label>
                                <input v-model="form.late_after_minutes" type="number" min="0" class="block w-full rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-slate-50/50">
                                <p class="text-xs text-slate-400 mt-1">เวลาอนุโลมก่อนถือว่ามาสาย</p>
                                <InputError class="mt-2" :message="form.errors.late_after_minutes" />
                            </div>

                            <!-- Break Duration -->
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">เวลาพัก (นาที) <span class="text-rose-500">*</span></label>
                                <input v-model="form.break_duration_minutes" type="number" min="0" class="block w-full rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-slate-50/50">
                                <p class="text-xs text-slate-400 mt-1">เช่น 60 นาที</p>
                                <InputError class="mt-2" :message="form.errors.break_duration_minutes" />
                            </div>

                            <!-- OT After -->
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">เริ่มคิด OT หลังเลิกงาน (นาที) <span class="text-rose-500">*</span></label>
                                <input v-model="form.ot_after_minutes" type="number" min="0" class="block w-full rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-slate-50/50">
                                <p class="text-xs text-slate-400 mt-1">จำนวนนาทีหลังเวลาออกงานก่อนเริ่มคิดโอที</p>
                                <InputError class="mt-2" :message="form.errors.ot_after_minutes" />
                            </div>
                            
                            <!-- Early Leave -->
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">ออกก่อนเวลาได้ (นาที) <span class="text-rose-500">*</span></label>
                                <input v-model="form.early_leave_before_minutes" type="number" min="0" class="block w-full rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-slate-50/50">
                                <p class="text-xs text-slate-400 mt-1">อนุโลมให้ออกก่อนเวลากี่นาที</p>
                                <InputError class="mt-2" :message="form.errors.early_leave_before_minutes" />
                            </div>

                            <!-- Working Days -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-bold text-slate-700 mb-2">วันทำงาน</label>
                                <input v-model="form.working_days" type="text" class="block w-full rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-slate-50/50" placeholder="เช่น จันทร์-เสาร์">
                                <InputError class="mt-2" :message="form.errors.working_days" />
                            </div>

                            <!-- Status -->
                            <div class="md:col-span-2 flex items-center gap-3 p-4 bg-slate-50 rounded-xl border border-slate-100">
                                <input v-model="form.is_active" type="checkbox" id="is_active" class="rounded border-slate-300 text-indigo-600 shadow-sm focus:ring-indigo-500 w-5 h-5">
                                <label for="is_active" class="text-sm font-bold text-slate-700 cursor-pointer">พนักงานสามารถใช้กะนี้ได้ (เปิดใช้งาน)</label>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-6 border-t border-slate-100">
                            <div>
                                <Link :href="route('shifts.destroy', shift.id)" method="delete" as="button" class="text-rose-500 hover:text-rose-700 text-sm font-bold hover:bg-rose-50 px-3 py-1.5 rounded-lg transition" onclick="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบกะทำงานนี้? อาจจะกระทบกับพนักงานที่ผูกกับกะนี้อยู่')">
                                    ลบกะทำงาน
                                </Link>
                            </div>
                            <div class="flex items-center gap-4">
                                <Link :href="route('shifts.index')" class="px-6 py-2.5 rounded-xl text-sm font-bold text-slate-600 hover:bg-slate-100 transition">
                                    ยกเลิก
                                </Link>
                                <button type="submit" :disabled="form.processing" class="inline-flex items-center gap-2 bg-indigo-600 border border-transparent rounded-xl px-6 py-2.5 text-sm font-bold text-white hover:bg-indigo-700 active:bg-indigo-800 transition shadow-md hover:shadow-lg disabled:opacity-50">
                                    บันทึกการแก้ไข
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
