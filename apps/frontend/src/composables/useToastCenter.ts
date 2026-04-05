import {computed, ref} from 'vue';

type ToastTone = 'error' | 'info' | 'success';

export type ToastMessage = {
    id: number;
    message: string;
    title: string;
    tone: ToastTone;
};

const toasts = ref<ToastMessage[]>([]);
let nextToastId = 1;

/**
 * Управляет глобальным стеком toast-уведомлений.
 */
export function useToastCenter() {
    const items = computed<ToastMessage[]>(() => toasts.value);

    /**
     * Добавляет toast в стек и автоматически убирает его через короткое время.
     */
    function pushToast(title: string, message: string, tone: ToastTone = 'info'): void {
        const toastId = nextToastId;
        nextToastId += 1;

        toasts.value = [
            ...toasts.value,
            {
                id: toastId,
                message,
                title,
                tone,
            },
        ];

        if (typeof window !== 'undefined') {
            window.setTimeout(() => {
                removeToast(toastId);
            }, 4000);
        }
    }

    /**
     * Удаляет toast из стека по идентификатору.
     */
    function removeToast(toastId: number): void {
        toasts.value = toasts.value.filter((toast) => toast.id !== toastId);
    }

    return {
        items,
        pushToast,
        removeToast,
    };
}
