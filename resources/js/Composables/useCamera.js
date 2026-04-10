import { ref } from 'vue';

export function useCamera() {
    const videoRef = ref(null);
    const canvasRef = ref(null);
    const stream = ref(null);
    const error = ref(null);
    const isCameraOn = ref(false);

    const startCamera = async () => {
        try {
            stream.value = await navigator.mediaDevices.getUserMedia({ 
                video: { facingMode: 'user' }, 
                audio: false 
            });
            if (videoRef.value) {
                videoRef.value.srcObject = stream.value;
            }
            isCameraOn.value = true;
            error.value = null;
        } catch (err) {
            error.value = 'Cannot access camera. Please check permissions.';
            console.error(err);
        }
    };

    const stopCamera = () => {
        if (stream.value) {
            stream.value.getTracks().forEach(track => track.stop());
            stream.value = null;
        }
        if (videoRef.value) {
            videoRef.value.srcObject = null;
        }
        isCameraOn.value = false;
    };

    const takePhoto = () => {
        if (!videoRef.value || !canvasRef.value || !isCameraOn.value) return null;
        
        const video = videoRef.value;
        const canvas = canvasRef.value;
        
        // Set canvas to exact video dimensions
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        
        const context = canvas.getContext('2d');
        context.drawImage(video, 0, 0, canvas.width, canvas.height);
        
        // Convert to blob (in the calling component we will use toBlob for better quality/size)
        return canvas.toDataURL('image/jpeg', 0.8);
    };

    return { videoRef, canvasRef, isCameraOn, error, startCamera, stopCamera, takePhoto };
}
