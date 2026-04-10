<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useCamera } from '@/Composables/useCamera';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const { videoRef, canvasRef, startCamera, stopCamera, takePhoto, isCameraOn } = useCamera();

const props = defineProps({
    modelValue: {
        type: [File, String, null],
        default: null
    }
});

const emit = defineEmits(['update:modelValue', 'capture']);

const photoData = ref(null);

onMounted(() => {
    startCamera();
});

onUnmounted(() => {
    stopCamera();
});

const capture = () => {
    photoData.value = takePhoto();
    if (photoData.value) {
        fetch(photoData.value)
            .then(res => res.blob())
            .then(blob => {
                const file = new File([blob], "capture.jpg", { type: "image/jpeg" });
                emit('update:modelValue', file);
                emit('capture', file);
            });
    }
};

const retake = () => {
    photoData.value = null;
    emit('update:modelValue', null);
};

// Expose a method to stop camera manually if needed by parent
defineExpose({
    stopCamera
});
</script>

<template>
    <div class="flex flex-col items-center w-full">
        <!-- Camera view with advanced premium styling -->
        <div class="relative w-full max-w-sm aspect-[3/4] sm:aspect-square md:aspect-[3/4] rounded-3xl overflow-hidden flex items-center justify-center p-2 bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 shadow-xl">
            <div class="absolute inset-2 bg-black rounded-2xl overflow-hidden">
                <video v-show="!photoData" ref="videoRef" autoplay playsinline class="absolute inset-0 w-full h-full object-cover"></video>
                <img v-if="photoData" :src="photoData" class="absolute inset-0 w-full h-full object-cover" />
                <canvas ref="canvasRef" style="display: none;"></canvas>
                
                <!-- Overlay scanning effect -->
                <div v-show="!photoData" class="absolute inset-0 border-[3px] border-white/20 rounded-2xl pointer-events-none"></div>
                <div v-show="!photoData" class="absolute top-1/4 left-0 w-full h-1 bg-white/40 shadow-[0_0_8px_2px_rgba(255,255,255,0.4)] animate-float" style="animation: float 3s ease-in-out infinite;"></div>
                
                <div v-if="!isCameraOn && !photoData" class="absolute inset-0 flex flex-col items-center justify-center text-white bg-black/80 backdrop-blur-sm z-20">
                    <svg class="w-10 h-10 animate-spin text-white mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span class="font-medium tracking-wide">กำลังเปิดกล้อง...</span>
                </div>
            </div>
        </div>
        
        <div class="mt-6 flex gap-3 w-full max-w-sm relative z-10">
            <PrimaryButton v-if="!photoData" @click="capture" type="button" class="w-full justify-center py-4 bg-slate-900 border-none shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all text-base tracking-widest font-bold uppercase rounded-xl" :disabled="!isCameraOn">
                ถ่ายรูปด่วน 📸
            </PrimaryButton>
            <button v-else @click="retake" type="button" class="w-full px-4 py-3 bg-white border-2 border-slate-200 text-slate-600 font-bold rounded-xl shadow-xs hover:bg-slate-50 hover:border-slate-300 transition-all">
                ถ่ายใหม่ ⟲
            </button>
        </div>
    </div>
</template>
<style scoped>
@keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(15px); }
}
</style>
