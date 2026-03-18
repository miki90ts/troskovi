<script setup lang="ts">
import { CheckCircle, Info, X, XCircle } from 'lucide-vue-next';
import { useToast } from '@/composables/useToast';

const { toasts, removeToast } = useToast();
</script>

<template>
    <Teleport to="body">
        <div class="fixed top-4 right-4 z-100 flex flex-col gap-2">
            <TransitionGroup
                enter-active-class="transition duration-300 ease-out"
                enter-from-class="translate-x-full opacity-0"
                enter-to-class="translate-x-0 opacity-100"
                leave-active-class="transition duration-200 ease-in"
                leave-from-class="translate-x-0 opacity-100"
                leave-to-class="translate-x-full opacity-0"
            >
                <div
                    v-for="toast in toasts"
                    :key="toast.id"
                    class="flex w-80 items-center gap-3 rounded-lg border bg-background p-4 shadow-lg"
                    :class="{
                        'border-green-500/30': toast.type === 'success',
                        'border-red-500/30': toast.type === 'error',
                        'border-blue-500/30': toast.type === 'info',
                    }"
                >
                    <CheckCircle
                        v-if="toast.type === 'success'"
                        class="h-5 w-5 shrink-0 text-green-500"
                    />
                    <XCircle
                        v-else-if="toast.type === 'error'"
                        class="h-5 w-5 shrink-0 text-red-500"
                    />
                    <Info v-else class="h-5 w-5 shrink-0 text-blue-500" />
                    <span class="flex-1 text-sm">{{ toast.message }}</span>
                    <button
                        class="shrink-0 text-muted-foreground hover:text-foreground"
                        @click="removeToast(toast.id)"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>
            </TransitionGroup>
        </div>
    </Teleport>
</template>
