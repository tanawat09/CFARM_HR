<script setup>
import { useForm, Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    holidays: Array,
});

const isModalOpen = ref(false);
const editingHoliday = ref(null);

const form = useForm({
    date: '',
    name: '',
});

const openModal = (holiday = null) => {
    editingHoliday.value = holiday;
    if (holiday) {
        form.date = holiday.date;
        form.name = holiday.name;
    } else {
        form.reset();
    }
    form.clearErrors();
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    setTimeout(() => {
        form.reset();
        editingHoliday.value = null;
    }, 200);
};

const submit = () => {
    if (editingHoliday.value) {
        form.put(route('settings.holidays.update', editingHoliday.value.id), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('settings.holidays.store'), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        });
    }
};

const deleteHoliday = (holiday) => {
    if (confirm(`คุณแน่ใจหรือไม่ที่จะลบวันหยุด "${holiday.name}"?`)) {
        useForm({}).delete(route('settings.holidays.destroy', holiday.id), {
            preserveScroll: true,
        });
    }
};

// Formatting helper
const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('th-TH', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};
</script>

<template>
    <Head title="ตั้งค่าวันหยุดบริษัท" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-emerald-800 leading-tight">
                    ตั้งค่าวันหยุดบริษัทประจำปี
                </h2>
                <button
                    @click="openModal()"
                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-emerald-500 to-teal-500 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:from-emerald-600 hover:to-teal-600 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition-all shadow-sm hover:shadow-md"
                >
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    เพิ่มวันหยุด
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <!-- Success Message -->
                <Transition
                    enter-active-class="transition ease-out duration-300"
                    enter-from-class="opacity-0 translate-y-[-10px]"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition ease-in duration-200"
                    leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 "
                >
                    <div v-if="$page.props.flash.message" class="p-4 mb-6 bg-emerald-100/80 backdrop-blur-sm border border-emerald-200 rounded-2xl shadow-sm flex items-center gap-3">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-emerald-800 font-medium tracking-wide">{{ $page.props.flash.message }}</span>
                    </div>
                </Transition>

                <!-- Data Table -->
                <div class="bg-white/70 backdrop-blur-lg overflow-hidden shadow-xl sm:rounded-3xl border border-white/50">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200/60">
                            <thead class="bg-slate-50/50">
                                <tr>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                        วันที่หยุด
                                    </th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                        ชื่อวันหยุด / เทศกาล
                                    </th>
                                    <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                        จัดการ
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-transparent">
                                <tr v-for="holiday in holidays" :key="holiday.id" class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center text-sm text-slate-900 font-medium">
                                            <svg class="w-4 h-4 mr-2 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                            {{ formatDate(holiday.date) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-slate-600 font-medium">{{ holiday.name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                                        <button 
                                            @click="openModal(holiday)"
                                            class="text-teal-600 hover:text-teal-900 transition-colors inline-flex items-center"
                                        >
                                            แก้ไข
                                        </button>
                                        <button 
                                            @click="deleteHoliday(holiday)"
                                            class="text-red-500 hover:text-red-700 transition-colors inline-flex items-center"
                                        >
                                            ลบ
                                        </button>
                                    </td>
                                </tr>
                                
                                <tr v-if="holidays.length === 0">
                                    <td colspan="3" class="px-6 py-12 text-center text-slate-500">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="w-12 h-12 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                            <p class="text-sm font-medium">ยังไม่มีการตั้งค่าวันหยุดบริษัท</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>

        <!-- Add/Edit Modal -->
        <Transition
            enter-active-class="ease-out duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="ease-in duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-show="isModalOpen" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" aria-hidden="true" @click="closeModal"></div>

                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <Transition
                        enter-active-class="ease-out duration-300"
                        enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        enter-to-class="opacity-100 translate-y-0 sm:scale-100"
                        leave-active-class="ease-in duration-200"
                        leave-from-class="opacity-100 translate-y-0 sm:scale-100"
                        leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    >
                        <div v-show="isModalOpen" class="inline-block align-bottom bg-white/90 backdrop-blur-xl rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full border border-white">
                            <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-slate-100/60">
                                <h3 class="text-lg leading-6 font-semibold text-slate-800" id="modal-title">
                                    {{ editingHoliday ? 'แก้ไขวันหยุด' : 'เพิ่มวันหยุดบริษัท' }}
                                </h3>
                            </div>
                            
                            <form @submit.prevent="submit">
                                <div class="px-4 py-5 sm:p-6 space-y-5">
                                    <div>
                                        <label for="date" class="block text-sm font-medium text-slate-700">วันที่หยุด</label>
                                        <input 
                                            type="date" 
                                            id="date" 
                                            v-model="form.date"
                                            class="mt-1 block w-full rounded-xl border-slate-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 transition-colors"
                                            required
                                        >
                                        <p v-if="form.errors.date" class="mt-2 text-sm text-red-600">{{ form.errors.date }}</p>
                                    </div>
                                    
                                    <div>
                                        <label for="name" class="block text-sm font-medium text-slate-700">ชื่อวันหยุด / เทศกาล</label>
                                        <input 
                                            type="text" 
                                            id="name" 
                                            v-model="form.name"
                                            placeholder="เช่น วันสงกรานต์, วันแรงงานแห่งชาติ"
                                            class="mt-1 block w-full rounded-xl border-slate-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 transition-colors"
                                            required
                                        >
                                        <p v-if="form.errors.name" class="mt-2 text-sm text-red-600">{{ form.errors.name }}</p>
                                    </div>
                                </div>
                                
                                <div class="bg-slate-50/50 px-4 py-4 sm:px-6 sm:flex sm:flex-row-reverse border-t border-slate-100/60">
                                    <button 
                                        type="submit" 
                                        :disabled="form.processing"
                                        class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-gradient-to-r from-emerald-500 to-teal-500 text-base font-medium text-white hover:from-emerald-600 hover:to-teal-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 sm:ml-3 sm:w-auto sm:text-sm transition-all"
                                    >
                                        {{ form.processing ? 'กำลังบันทึก...' : 'บันทึก' }}
                                    </button>
                                    <button 
                                        type="button" 
                                        @click="closeModal"
                                        class="mt-3 w-full inline-flex justify-center rounded-xl border border-slate-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors"
                                    >
                                        ยกเลิก
                                    </button>
                                </div>
                            </form>
                        </div>
                    </Transition>
                </div>
            </div>
        </Transition>
    </AuthenticatedLayout>
</template>
