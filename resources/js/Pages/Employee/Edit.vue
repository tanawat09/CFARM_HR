<script setup>
import { ref } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    employee: Object,
    departments: Array,
    positions: Array,
    branches: Array,
    shifts: Array,
    supervisors: Array,
    employmentStatuses: Array, // [{value, label}]
});

const form = useForm({
    first_name: props.employee.first_name || '',
    last_name: props.employee.last_name || '',
    phone: props.employee.phone || '',
    department_id: props.employee.department_id || '',
    position_id: props.employee.position_id || '',
    branch_id: props.employee.branch_id || '',
    shift_id: props.employee.shift_id || '',
    supervisor_id: props.employee.supervisor_id || '',
    hire_date: props.employee.hire_date?.split('T')[0] || '',
    employment_status: props.employee.employment_status?.value || props.employee.employment_status || 'active',
    password: '',
    password_confirmation: '',
});

const showPassword = ref(false);

const submit = () => {
    form.put(route('employees.update', props.employee.id));
};
</script>

<template>
    <Head :title="`แก้ไขพนักงาน: ${employee.first_name} ${employee.last_name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <Link :href="route('employees.index')" class="text-slate-400 hover:text-indigo-600 transition bg-white p-2 rounded-xl shadow-sm border border-slate-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    </Link>
                    <h2 class="font-bold text-2xl text-slate-800 leading-tight tracking-tight">✏️ แก้ไขพนักงาน</h2>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

                <!-- Employee Header -->
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 mb-6 flex items-center gap-5">
                    <div class="w-16 h-16 rounded-full bg-indigo-100 border-2 border-indigo-200 flex items-center justify-center text-2xl font-bold text-indigo-600 shrink-0">
                        {{ employee.first_name?.charAt(0) }}{{ employee.last_name?.charAt(0) }}
                    </div>
                    <div>
                        <h3 class="font-extrabold text-xl text-slate-800">{{ employee.first_name }} {{ employee.last_name }}</h3>
                        <p class="text-sm text-slate-500">รหัสพนง. <span class="font-mono font-bold text-indigo-600">{{ employee.employee_code }}</span> · {{ employee.user?.email }}</p>
                    </div>
                </div>

                <!-- Edit Form -->
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                    <form @submit.prevent="submit" class="p-8 space-y-8">

                        <!-- Section: Personal Info -->
                        <div>
                            <h3 class="font-bold text-base text-slate-800 mb-4 flex items-center gap-2 pb-3 border-b border-slate-100">
                                👤 ข้อมูลส่วนตัว
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                                <div>
                                    <label class="block font-bold text-xs text-slate-500 mb-1">ชื่อจริง <span class="text-rose-500">*</span></label>
                                    <input type="text" v-model="form.first_name" class="w-full rounded-xl border-slate-200 text-sm focus:ring-indigo-500" required>
                                    <InputError :message="form.errors.first_name" class="mt-1" />
                                </div>
                                <div>
                                    <label class="block font-bold text-xs text-slate-500 mb-1">นามสกุล <span class="text-rose-500">*</span></label>
                                    <input type="text" v-model="form.last_name" class="w-full rounded-xl border-slate-200 text-sm focus:ring-indigo-500" required>
                                    <InputError :message="form.errors.last_name" class="mt-1" />
                                </div>
                                <div>
                                    <label class="block font-bold text-xs text-slate-500 mb-1">เบอร์โทร</label>
                                    <input type="text" v-model="form.phone" class="w-full rounded-xl border-slate-200 text-sm focus:ring-indigo-500" placeholder="08x-xxx-xxxx">
                                    <InputError :message="form.errors.phone" class="mt-1" />
                                </div>
                            </div>
                        </div>

                        <!-- Section: Organization -->
                        <div>
                            <h3 class="font-bold text-base text-slate-800 mb-4 flex items-center gap-2 pb-3 border-b border-slate-100">
                                🏢 ข้อมูลองค์กร
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label class="block font-bold text-xs text-slate-500 mb-1">แผนก <span class="text-rose-500">*</span></label>
                                    <select v-model="form.department_id" class="w-full rounded-xl border-slate-200 text-sm focus:ring-indigo-500" required>
                                        <option value="" disabled>-- เลือกแผนก --</option>
                                        <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option>
                                    </select>
                                    <InputError :message="form.errors.department_id" class="mt-1" />
                                </div>
                                <div>
                                    <label class="block font-bold text-xs text-slate-500 mb-1">ตำแหน่ง <span class="text-rose-500">*</span></label>
                                    <select v-model="form.position_id" class="w-full rounded-xl border-slate-200 text-sm focus:ring-indigo-500" required>
                                        <option value="" disabled>-- เลือกตำแหน่ง --</option>
                                        <option v-for="p in positions" :key="p.id" :value="p.id">{{ p.name }}</option>
                                    </select>
                                    <InputError :message="form.errors.position_id" class="mt-1" />
                                </div>
                                <div>
                                    <label class="block font-bold text-xs text-slate-500 mb-1">กะการทำงาน <span class="text-rose-500">*</span></label>
                                    <select v-model="form.shift_id" class="w-full rounded-xl border-slate-200 text-sm focus:ring-indigo-500" required>
                                        <option value="" disabled>-- เลือกกะ --</option>
                                        <option v-for="s in shifts" :key="s.id" :value="s.id">{{ s.name }} ({{ s.start_time?.substring(0,5) }} - {{ s.end_time?.substring(0,5) }})</option>
                                    </select>
                                    <InputError :message="form.errors.shift_id" class="mt-1" />
                                </div>
                                <div>
                                    <label class="block font-bold text-xs text-slate-500 mb-1">หัวหน้างาน (Supervisor)</label>
                                    <select v-model="form.supervisor_id" class="w-full rounded-xl border-slate-200 text-sm focus:ring-indigo-500">
                                        <option value="">-- ไม่มีหัวหน้างาน (เช่น CEO) --</option>
                                        <option v-for="sup in supervisors" :key="sup.id" :value="sup.id">{{ sup.first_name }} {{ sup.last_name }}</option>
                                    </select>
                                    <InputError :message="form.errors.supervisor_id" class="mt-1" />
                                </div>
                            </div>
                        </div>

                        <!-- Section: Employment -->
                        <div>
                            <h3 class="font-bold text-base text-slate-800 mb-4 flex items-center gap-2 pb-3 border-b border-slate-100">
                                📋 สถานะการจ้างงาน
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label class="block font-bold text-xs text-slate-500 mb-1">วันที่เริ่มงาน <span class="text-rose-500">*</span></label>
                                    <input type="date" v-model="form.hire_date" class="w-full rounded-xl border-slate-200 text-sm focus:ring-indigo-500" required>
                                    <InputError :message="form.errors.hire_date" class="mt-1" />
                                </div>
                                <div>
                                    <label class="block font-bold text-xs text-slate-500 mb-1">สถานะ <span class="text-rose-500">*</span></label>
                                    <select v-model="form.employment_status" class="w-full rounded-xl border-slate-200 text-sm focus:ring-indigo-500" required>
                                        <option v-for="st in employmentStatuses" :key="st.value" :value="st.value">{{ st.label }}</option>
                                    </select>
                                    <InputError :message="form.errors.employment_status" class="mt-1" />
                                </div>
                            </div>
                        </div>

                        <!-- Section: Account Settings (Password Change) -->
                        <div>
                            <h3 class="font-bold text-base text-slate-800 mb-4 flex items-center gap-2 pb-3 border-b border-slate-100">
                                🔐 ตั้งค่าบัญชี (เปลี่ยนรหัสผ่าน)
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label class="block font-bold text-xs text-slate-500 mb-1">รหัสผ่านใหม่ (ว่างไว้หากไม่ต้องการเปลี่ยน)</label>
                                    <div class="relative">
                                        <input :type="showPassword ? 'text' : 'password'" v-model="form.password" class="w-full rounded-xl border-slate-200 text-sm focus:ring-indigo-500 pr-10" autocomplete="new-password">
                                        <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-indigo-600 transition">
                                            <svg v-if="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7 1.274-4.057-5.064-7-9.542-7-4.477 0-8.268-2.943-9.542-7z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18"></path></svg>
                                        </button>
                                    </div>
                                    <InputError :message="form.errors.password" class="mt-1" />
                                </div>
                                <div>
                                    <label class="block font-bold text-xs text-slate-500 mb-1">ยืนยันรหัสผ่านใหม่</label>
                                    <div class="relative">
                                        <input :type="showPassword ? 'text' : 'password'" v-model="form.password_confirmation" class="w-full rounded-xl border-slate-200 text-sm focus:ring-indigo-500 pr-10" autocomplete="new-password">
                                        <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-indigo-600 transition">
                                            <svg v-if="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7 1.274-4.057-5.064-7-9.542-7-4.477 0-8.268-2.943-9.542-7z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18"></path></svg>
                                        </button>
                                    </div>
                                    <InputError :message="form.errors.password_confirmation" class="mt-1" />
                                </div>
                            </div>
                            <p class="mt-2 text-xs text-slate-400">กรอกรหัสผ่านหากต้องการเปลี่ยนรหัสผ่านในการเข้าสู่ระบบของพนักงานรายนี้</p>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-end gap-4 pt-6 border-t border-slate-100">
                            <Link :href="route('employees.index')" class="text-slate-500 font-bold text-sm hover:text-slate-700 transition px-4 py-2">ยกเลิก</Link>
                            <button type="submit" :disabled="form.processing" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 rounded-xl px-8 py-3 text-sm font-bold text-white shadow-md hover:shadow-lg transition disabled:opacity-50">
                                <svg v-if="form.processing" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                {{ form.processing ? 'กำลังบันทึก...' : '💾 บันทึกการแก้ไข' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
