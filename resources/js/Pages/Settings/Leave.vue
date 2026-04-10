<script setup>
import { ref } from 'vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({ policies: Array });

const showModal = ref(false);
const editingPolicy = ref(null);
const confirmDelete = ref(null);

const emptyForm = {
    key: '', name: '', icon: '📋', quota_days: 6,
    requires_reason: true, requires_attachment_after_days: null,
    probation_allowed: true, is_tenure_based: false,
    tenure_threshold_years: null, tenure_bonus_days: null, is_active: true,
};
const form = useForm({ ...emptyForm });

const emojiOptions = ['🌴','🏥','📋','👶','📝','🎓','⚖️','✈️','🏠','💒','🎖️','🙏','💼','⛪'];

const openCreate = () => {
    editingPolicy.value = null;
    form.reset(); Object.assign(form, { ...emptyForm }); form.clearErrors();
    showModal.value = true;
};
const openEdit = (p) => {
    editingPolicy.value = p;
    Object.keys(emptyForm).forEach(k => form[k] = p[k]);
    form.clearErrors();
    showModal.value = true;
};
const submitForm = () => {
    if (editingPolicy.value) {
        form.put(route('settings.leave.update', editingPolicy.value.id), { onSuccess: () => showModal.value = false });
    } else {
        form.post(route('settings.leave.store'), { onSuccess: () => showModal.value = false });
    }
};
const deletePolicy = (p) => {
    router.delete(route('settings.leave.destroy', p.id), { onSuccess: () => confirmDelete.value = null });
};
</script>

<template>
    <Head title="ตั้งค่าระบบการลา" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-bold text-2xl text-slate-800 leading-tight tracking-tight">⚙️ ตั้งค่าประเภทการลา</h2>
                <button @click="openCreate" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl px-5 py-2.5 text-sm font-bold shadow-md hover:shadow-lg transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    เพิ่มประเภทการลา
                </button>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">

                <!-- Flash -->
                <div v-if="$page.props.flash?.message" class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl flex items-center gap-2 text-sm font-semibold">
                    ✅ {{ $page.props.flash.message }}
                </div>
                <div v-if="$page.props.errors?.delete" class="mb-4 bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl flex items-center gap-2 text-sm font-semibold">
                    ⚠️ {{ $page.props.errors.delete }}
                </div>

                <!-- Simple Table -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">ประเภท</th>
                                <th class="px-4 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">โควตา/ปี</th>
                                <th class="px-4 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center hidden md:table-cell">เหตุผล</th>
                                <th class="px-4 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center hidden md:table-cell">เอกสาร</th>
                                <th class="px-4 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center hidden md:table-cell">ทดลองงาน</th>
                                <th class="px-4 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">สถานะ</th>
                                <th class="px-4 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="p in policies" :key="p.id" class="hover:bg-slate-50/50 transition" :class="{'opacity-50': !p.is_active}">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <span class="text-2xl">{{ p.icon }}</span>
                                        <div>
                                            <div class="font-bold text-slate-800">{{ p.name }}</div>
                                            <div class="text-xs text-slate-400 font-mono">{{ p.key }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <span class="font-extrabold text-indigo-600 text-lg">{{ p.quota_days }}</span>
                                    <span class="text-xs text-slate-400 ml-1">วัน</span>
                                    <div v-if="p.is_tenure_based" class="text-xs text-teal-600 font-semibold mt-0.5">≥{{ p.tenure_threshold_years }}ปี → {{ p.tenure_bonus_days }} วัน</div>
                                </td>
                                <td class="px-4 py-4 text-center hidden md:table-cell">
                                    <span v-if="p.requires_reason" class="text-amber-500 font-bold text-sm">บังคับ</span>
                                    <span v-else class="text-slate-300 text-sm">—</span>
                                </td>
                                <td class="px-4 py-4 text-center hidden md:table-cell">
                                    <span v-if="p.requires_attachment_after_days" class="text-amber-500 font-bold text-sm">≥{{ p.requires_attachment_after_days }} วัน</span>
                                    <span v-else class="text-slate-300 text-sm">—</span>
                                </td>
                                <td class="px-4 py-4 text-center hidden md:table-cell">
                                    <span v-if="p.probation_allowed" class="text-emerald-500 text-sm font-bold">ลาได้</span>
                                    <span v-else class="text-rose-500 text-sm font-bold">ไม่ได้</span>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <span v-if="p.is_active" class="bg-emerald-100 text-emerald-700 text-xs font-bold px-2 py-1 rounded-full">เปิด</span>
                                    <span v-else class="bg-rose-100 text-rose-600 text-xs font-bold px-2 py-1 rounded-full">ปิด</span>
                                </td>
                                <td class="px-4 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click="openEdit(p)" class="text-indigo-600 hover:text-indigo-800 font-bold text-sm transition" title="แก้ไข">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </button>
                                        <template v-if="confirmDelete === p.id">
                                            <button @click="deletePolicy(p)" class="text-white bg-rose-600 hover:bg-rose-700 text-xs font-bold px-2 py-1 rounded-lg">ยืนยัน</button>
                                            <button @click="confirmDelete = null" class="text-slate-400 hover:text-slate-600 text-xs font-bold">ยกเลิก</button>
                                        </template>
                                        <button v-else @click="confirmDelete = p.id" class="text-rose-400 hover:text-rose-600 transition" title="ลบ">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div v-if="policies.length === 0" class="py-12 text-center text-slate-400">
                        <div class="text-4xl mb-2">📋</div>
                        <p class="font-bold">ยังไม่มีประเภทการลา กด "เพิ่มประเภทการลา" เพื่อเริ่มต้น</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <Teleport to="body">
            <div v-if="showModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="showModal = false"></div>
                <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
                    <div class="sticky top-0 bg-white border-b p-5 flex justify-between items-center rounded-t-2xl z-10">
                        <h3 class="font-bold text-lg text-slate-800">{{ editingPolicy ? '✏️ แก้ไข' : '➕ เพิ่มประเภทการลา' }}</h3>
                        <button @click="showModal = false" class="text-slate-400 hover:text-slate-700"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                    </div>
                    <form @submit.prevent="submitForm" class="p-5 space-y-5">
                        
                        <!-- Row 1: Icon + Key + Name -->
                        <div class="flex gap-4 items-start">
                            <div class="shrink-0">
                                <label class="block font-bold text-xs text-slate-500 mb-1">ไอคอน</label>
                                <div class="grid grid-cols-7 gap-1 w-fit">
                                    <label v-for="e in emojiOptions" :key="e" class="cursor-pointer">
                                        <input type="radio" :value="e" v-model="form.icon" class="sr-only peer">
                                        <div class="text-lg text-center p-1 rounded-lg border peer-checked:border-indigo-500 peer-checked:bg-indigo-50 hover:bg-slate-50 transition">{{ e }}</div>
                                    </label>
                                </div>
                            </div>
                            <div class="flex-1 space-y-3">
                                <div>
                                    <label class="block font-bold text-xs text-slate-500 mb-1">รหัส (key) *</label>
                                    <input type="text" v-model="form.key" :disabled="!!editingPolicy" :class="{'bg-slate-100': editingPolicy}" class="w-full rounded-lg border-slate-200 text-sm focus:ring-indigo-500 font-mono" placeholder="sick_leave" required>
                                    <InputError :message="form.errors.key" class="mt-1" />
                                </div>
                                <div>
                                    <label class="block font-bold text-xs text-slate-500 mb-1">ชื่อภาษาไทย *</label>
                                    <input type="text" v-model="form.name" class="w-full rounded-lg border-slate-200 text-sm focus:ring-indigo-500" placeholder="ลาป่วย" required>
                                    <InputError :message="form.errors.name" class="mt-1" />
                                </div>
                            </div>
                        </div>

                        <!-- Quota Slider -->
                        <div>
                            <label class="block font-bold text-xs text-slate-500 mb-1">โควตาต่อปี</label>
                            <div class="flex items-center gap-3">
                                <input type="range" v-model="form.quota_days" min="0" max="365" class="flex-1 accent-indigo-500">
                                <span class="bg-indigo-50 text-indigo-700 font-extrabold px-3 py-1 rounded-lg text-sm min-w-[60px] text-center">{{ form.quota_days }} วัน</span>
                            </div>
                        </div>

                        <!-- Simple Toggles -->
                        <div class="grid grid-cols-2 gap-3">
                            <label class="flex items-center gap-2 p-3 bg-slate-50 rounded-xl cursor-pointer text-sm">
                                <input type="checkbox" v-model="form.requires_reason" class="rounded text-indigo-600 focus:ring-indigo-500">
                                <span class="font-semibold text-slate-700">ต้องระบุเหตุผล</span>
                            </label>
                            <label class="flex items-center gap-2 p-3 bg-slate-50 rounded-xl cursor-pointer text-sm">
                                <input type="checkbox" v-model="form.probation_allowed" class="rounded text-indigo-600 focus:ring-indigo-500">
                                <span class="font-semibold text-slate-700">ทดลองงานลาได้</span>
                            </label>
                            <label class="flex items-center gap-2 p-3 bg-slate-50 rounded-xl cursor-pointer text-sm">
                                <input type="checkbox" v-model="form.is_active" class="rounded text-emerald-600 focus:ring-emerald-500">
                                <span class="font-semibold text-slate-700">เปิดใช้งาน</span>
                            </label>
                            <label class="flex items-center gap-2 p-3 bg-slate-50 rounded-xl cursor-pointer text-sm">
                                <input type="checkbox" v-model="form.is_tenure_based" class="rounded text-indigo-600 focus:ring-indigo-500">
                                <span class="font-semibold text-slate-700">ตามอายุงาน</span>
                            </label>
                        </div>

                        <!-- Tenure Config -->
                        <div v-if="form.is_tenure_based" class="bg-indigo-50 rounded-xl p-4 grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-bold text-xs text-indigo-800 mb-1">เกณฑ์อายุงาน (ปี)</label>
                                <input type="number" v-model="form.tenure_threshold_years" min="1" max="50" class="w-full rounded-lg border-indigo-200 text-sm">
                            </div>
                            <div>
                                <label class="block font-bold text-xs text-indigo-800 mb-1">โควตาหลังถึงเกณฑ์ (วัน)</label>
                                <input type="number" v-model="form.tenure_bonus_days" min="0" max="365" class="w-full rounded-lg border-indigo-200 text-sm">
                            </div>
                        </div>

                        <!-- Attachment Threshold -->
                        <div>
                            <label class="block font-bold text-xs text-slate-500 mb-1">ต้องแนบเอกสารเมื่อลาตั้งแต่ (วัน) <span class="font-normal text-slate-400">— เว้นว่าง = ไม่ต้อง</span></label>
                            <input type="number" v-model="form.requires_attachment_after_days" min="1" max="30" class="w-32 rounded-lg border-slate-200 text-sm" placeholder="เช่น 3">
                        </div>

                        <!-- Actions -->
                        <div class="flex justify-end gap-3 pt-3 border-t">
                            <button type="button" @click="showModal = false" class="text-slate-500 font-bold text-sm px-4 py-2 hover:text-slate-700 transition">ยกเลิก</button>
                            <button type="submit" :disabled="form.processing" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm px-6 py-2.5 rounded-xl shadow transition disabled:opacity-50">
                                {{ form.processing ? 'กำลังบันทึก...' : (editingPolicy ? 'บันทึก' : 'เพิ่ม') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>
