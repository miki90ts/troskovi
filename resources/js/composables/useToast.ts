import { ref } from 'vue';

export type Toast = {
    id: number;
    message: string;
    type: 'success' | 'error' | 'info';
};

const toasts = ref<Toast[]>([]);
let nextId = 0;

export function useToast() {
    function addToast(
        message: string,
        type: Toast['type'] = 'success',
        duration = 3000,
    ) {
        const id = nextId++;
        toasts.value.push({ id, message, type });

        setTimeout(() => {
            removeToast(id);
        }, duration);
    }

    function removeToast(id: number) {
        toasts.value = toasts.value.filter((t) => t.id !== id);
    }

    function success(message: string) {
        addToast(message, 'success');
    }

    function error(message: string) {
        addToast(message, 'error', 5000);
    }

    function info(message: string) {
        addToast(message, 'info');
    }

    return { toasts, addToast, removeToast, success, error, info };
}
