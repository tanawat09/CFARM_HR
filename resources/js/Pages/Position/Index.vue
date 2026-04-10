<script setup>
import { ref } from 'vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({ positions: Array, departments: Array });

const showModal = ref(false);
const editing = ref(null);
const confirmDelete = ref(null);

const form = useForm({ name: '', department_id: '', is_active: true });

const openCreate = () => { editing.value = null; form.reset(); form.is_active = true; form.clearErrors(); showModal.value = true; };
const openEdit = (p) => { editing.value = p; form.name = p.name; form.department_id = p.department_id || ''; form.is_active = p.is_active; form.clearErrors(); showModal.value = true; };
const submitForm = () => {
    if (editing.value) {
        form.put(route('positions.update', editing.value.id), { onSuccess: () => showModal.value = false });
    } else {
        form.post(route('positions.store'), { onSuccess: () => showModal.value = false });
    }
};
const del = (p) => router.delete(route('positions.destroy', p.id), { onSuccess: () => confirmDelete.value = null });
</script>

<template>
    <Head title="จัดการตำแหน่ง" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-bold text-2xl text-slate-800">💼 จัดการตำแหน่ง</h2>
                <button @click="openCreate" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl px-5 py-2.5 text-sm font-bold shadow-md transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    เพิ่มตำแหน่ง
                </button>
            </div>
        </template>
        <div class="py-8">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div v-if="$page.props.flash?.message" class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm font-semibold">✅ {{ $page.props.flash.message }}</div>
                <div v-if="$page.props.errors?.delete" class="mb-4 bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl text-sm font-semibold">⚠️ {{ $page.props.errors.delete }}</div>

                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">ชื่อตำแหน่ง</th>
                                <th class="px-4 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">แผนก</th>
                                <th class="px-4 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">พนักงาน</th>
                                <th class="px-4 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">สถานะ</th>
                                <th class="px-4 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="p in positions" :key="p.id" class="hover:bg-slate-50/50 transition">
                                <td class="px-6 py-4 font-bold text-slate-800">{{ p.name }}</td>
                                <td class="px-4 py-4 text-sm text-slate-600">{{ p.department?.name || '—' }}</td>
                                <td class="px-4 py-4 text-center"><span class="bg-indigo-100 text-indigo-700 text-xs font-bold px-2 py-1 rounded-full">{{ p.employees_count }} คน</span></td>
                                <td class="px-4 py-4 text-center">
                                    <span :class="p.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-600'" class="text-xs font-bold px-2 py-1 rounded-full">{{ p.is_active ? 'เปิด' : 'ปิด' }}</span>
                                </td>
                                <td class="px-4 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click="openEdit(p)" class="text-indigo-600 hover:text-indigo-800 font-bold text-sm">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </button>
                                        <template v-if="confirmDelete === p.id">
                                            <button @click="del(p)" class="text-white bg-rose-600 hover:bg-rose-700 text-xs font-bold px-2 py-1 rounded-lg">ยืนยัน</button>
                                            <button @click="confirmDelete = null" class="text-slate-400 text-xs font-bold">ยกเลิก</button>
                                        </template>
                                        <button v-else @click="confirmDelete = p.id" class="text-rose-400 hover:text-rose-600">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div v-if="positions.length === 0" class="py-12 text-center text-slate-400 font-bold">ยังไม่มีตำแหน่ง</div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <Teleport to="body">
            <div v-if="showModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="showModal = false"></div>
                <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md">
                    <div class="border-b p-5 flex justify-between items-center">
                        <h3 class="font-bold text-lg text-slate-800">{{ editing ? '✏️ แก้ไขตำแหน่ง' : '➕ เพิ่มตำแหน่ง' }}</h3>
                        <button @click="showModal = false" class="text-slate-400 hover:text-slate-700"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                    </div>
                    <form @submit.prevent="submitForm" class="p-5 space-y-4">
                        <div>
                            <label class="block font-bold text-xs text-slate-500 mb-1">ชื่อตำแหน่ง *</label>
                            <input type="text" v-model="form.name" class="w-full rounded-lg border-slate-200 text-sm focus:ring-indigo-500" required>
                            <InputError :message="form.errors.name" class="mt-1" />
                        </div>
                        <div>
                            <label class="block font-bold text-xs text-slate-500 mb-1">สังกัดแผนก</label>
                            <select v-model="form.department_id" class="w-full rounded-lg border-slate-200 text-sm focus:ring-indigo-500">
                                <option value="">— ไม่ระบุ —</option>
                                <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option>
                            </select>
                        </div>
                        <label class="flex items-center gap-2 p-3 bg-slate-50 rounded-xl cursor-pointer text-sm">
                            <input type="checkbox" v-model="form.is_active" class="rounded text-emerald-600 focus:ring-emerald-500">
                            <span class="font-semibold text-slate-700">เปิดใช้งาน</span>
                        </label>
                        <div class="flex justify-end gap-3 pt-3 border-t">
                            <button type="button" @click="showModal = false" class="text-slate-500 font-bold text-sm px-4 py-2">ยกเลิก</button>
                            <button type="submit" :disabled="form.processing" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm px-6 py-2.5 rounded-xl shadow transition disabled:opacity-50">
                                {{ form.processing ? 'กำลังบันทึก...' : (editing ? 'บันทึก' : 'เพิ่ม') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>
