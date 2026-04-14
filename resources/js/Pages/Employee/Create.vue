<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    departments: Array,
    positions: Array,
    branches: Array,
    shifts: Array,
    supervisors: Array,
    employmentStatuses: Array,
    checkInModes: Array
});

const form = useForm({
    employee_code: '',
    first_name: '',
    last_name: '',
    email: '',
    password: '',
    password_confirmation: '',
    department_id: '',
    position_id: '',
    branch_id: '',
    shift_id: '',
    supervisor_id: '',
    line_user_id: '',
    hire_date: new Date().toISOString().split('T')[0]
});

const submit = () => {
    form.post(route('employees.store'));
};
</script>

<template>
    <Head title="เพิ่มพนักงานใหม่" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-bold text-2xl text-slate-800 leading-tight tracking-tight">👤 เพิ่มพนักงานใหม่</h2>
                <Link :href="route('employees.index')" class="text-sm font-semibold text-slate-500 hover:text-slate-800 transition flex items-center gap-1 bg-white px-4 py-2 rounded-xl shadow-sm border border-slate-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    กลับสู่หน้ารายการพนักงาน
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="glass sm:rounded-3xl p-8 relative overflow-hidden border border-white/60 shadow-xl">
                    <!-- Background blobs -->
                    <div class="absolute -right-20 -top-20 w-80 h-80 bg-indigo-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 pointer-events-none"></div>

                    <form @submit.prevent="submit" class="relative z-10 bg-white/70 backdrop-blur p-8 rounded-2xl border border-slate-100 shadow-sm">
                        
                        <div class="mb-8 pb-4 border-b border-slate-200">
                            <h3 class="text-lg font-bold text-indigo-700 mb-1 flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                ข้อมูลเบื้องต้น และ ผู้ใช้งาน (User Account)
                            </h3>
                            <p class="text-sm text-slate-500">สำหรับใช้เข้าสู่ระบบด้วยรหัสผ่านส่วนตัว</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                            <div>
                                <label class="block font-bold text-sm text-slate-700 mb-2">รหัสพนักงาน <span class="text-rose-500">*</span></label>
                                <input type="text" v-model="form.employee_code" class="w-full rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition" placeholder="เช่น EMP001" required>
                                <InputError :message="form.errors.employee_code" class="mt-2" />
                            </div>
                            <div>
                                <label class="block font-bold text-sm text-slate-700 mb-2">วันที่เริ่มงาน <span class="text-rose-500">*</span></label>
                                <input type="date" v-model="form.hire_date" class="w-full rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition cursor-pointer" required>
                                <InputError :message="form.errors.hire_date" class="mt-2" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                            <div>
                                <label class="block font-bold text-sm text-slate-700 mb-2">ชื่อจริง <span class="text-rose-500">*</span></label>
                                <input type="text" v-model="form.first_name" class="w-full rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition" required>
                                <InputError :message="form.errors.first_name" class="mt-2" />
                            </div>
                            <div>
                                <label class="block font-bold text-sm text-slate-700 mb-2">นามสกุล <span class="text-rose-500">*</span></label>
                                <input type="text" v-model="form.last_name" class="w-full rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition" required>
                                <InputError :message="form.errors.last_name" class="mt-2" />
                            </div>
                            <div class="md:col-span-2">
                                <label class="block font-bold text-sm text-slate-700 mb-2">อีเมล (ใช้สำหรับ Login เข้าสู่ระบบ) <span class="text-rose-500">*</span></label>
                                <input type="email" v-model="form.email" class="w-full rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition" required>
                                <InputError :message="form.errors.email" class="mt-2" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-12 pb-8 border-b border-slate-200">
                             <div>
                                <label class="block font-bold text-sm text-slate-700 mb-2">รหัสผ่าน <span class="text-rose-500">*</span></label>
                                <input type="password" v-model="form.password" class="w-full rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition" required autocomplete="new-password">
                                <InputError :message="form.errors.password" class="mt-2" />
                            </div>
                            <div>
                                <label class="block font-bold text-sm text-slate-700 mb-2">ยืนยันรหัสผ่าน <span class="text-rose-500">*</span></label>
                                <input type="password" v-model="form.password_confirmation" class="w-full rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition" required autocomplete="new-password">
                                <InputError :message="form.errors.password_confirmation" class="mt-2" />
                            </div>
                        </div>

                        <div class="mb-8 pb-4 border-b border-slate-200">
                            <h3 class="text-lg font-bold text-teal-700 mb-1 flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                ข้อมูลในองค์กร
                            </h3>
                            <p class="text-sm text-slate-500">สำหรับกำหนดสิทธิ์และกฎการลงเวลาทำงาน</p>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                            <div>
                                <label class="block font-bold text-sm text-slate-700 mb-2">แผนก <span class="text-rose-500">*</span></label>
                                <select v-model="form.department_id" class="w-full rounded-xl border-slate-200 shadow-sm focus:border-teal-500 focus:ring-teal-500 transition bg-white" required>
                                    <option value="" disabled>-- เลือกแผนก --</option>
                                    <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option>
                                </select>
                                <InputError :message="form.errors.department_id" class="mt-2" />
                            </div>
                            <div>
                                <label class="block font-bold text-sm text-slate-700 mb-2">ตำแหน่ง <span class="text-rose-500">*</span></label>
                                <select v-model="form.position_id" class="w-full rounded-xl border-slate-200 shadow-sm focus:border-teal-500 focus:ring-teal-500 transition bg-white" required>
                                    <option value="" disabled>-- เลือกตำแหน่ง --</option>
                                    <option v-for="p in positions" :key="p.id" :value="p.id">{{ p.name }}</option>
                                </select>
                                <InputError :message="form.errors.position_id" class="mt-2" />
                            </div>
                            <div>
                                <label class="block font-bold text-sm text-slate-700 mb-2">กะการทำงาน (Shift) <span class="text-rose-500">*</span></label>
                                <select v-model="form.shift_id" class="w-full rounded-xl border-slate-200 shadow-sm focus:border-teal-500 focus:ring-teal-500 transition bg-white" required>
                                    <option value="" disabled>-- เลือกกะเวลาเข้างาน --</option>
                                    <option v-for="s in shifts" :key="s.id" :value="s.id">{{ s.name }} ({{ s.start_time.substring(0,5) }} - {{ s.end_time.substring(0,5) }})</option>
                                </select>
                                <InputError :message="form.errors.shift_id" class="mt-2" />
                            </div>
                            <div>
                                <label class="block font-bold text-sm text-slate-700 mb-2">หัวหน้างาน (Supervisor)</label>
                                <select v-model="form.supervisor_id" class="w-full rounded-xl border-slate-200 shadow-sm focus:border-teal-500 focus:ring-teal-500 transition bg-white">
                                    <option value="">-- ไม่มีหัวหน้างาน (เช่น CEO) --</option>
                                    <option v-for="sup in supervisors" :key="sup.id" :value="sup.id">{{ sup.first_name }} {{ sup.last_name }}</option>
                                </select>
                                <InputError :message="form.errors.supervisor_id" class="mt-2" />
                            </div>
                            <div class="md:col-span-2">
                                <label class="block font-bold text-sm text-slate-700 mb-2">LINE userId</label>
                                <input type="text" v-model="form.line_user_id" class="w-full rounded-xl border-slate-200 shadow-sm focus:border-teal-500 focus:ring-teal-500 transition" placeholder="เช่น Uxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx">
                                <p class="mt-1 text-xs text-slate-400">กรอกเฉพาะพนักงานที่ต้องรับแจ้งเตือนจาก LINE OA เช่น หัวหน้างาน</p>
                                <InputError :message="form.errors.line_user_id" class="mt-2" />
                            </div>
                        </div>

                        <div class="pt-6 border-t border-slate-100 flex items-center justify-end gap-4 mt-12 bg-slate-50 p-6 rounded-2xl -m-8 mt-4 rounded-t-none">
                            <Link :href="route('employees.index')" class="text-slate-500 font-bold hover:text-slate-700 transition px-4 py-2">
                                ยกเลิก
                            </Link>
                            <button type="submit" :disabled="form.processing" class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl px-8 py-3.5 text-sm font-bold text-white shadow-lg hover:shadow-xl transition transform hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg v-if="form.processing" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                
                                <span v-else>💾 บันทึกและสร้างการใช้งานระบบ</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
