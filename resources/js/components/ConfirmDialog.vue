<script setup lang="ts">
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';

defineProps<{
    open: boolean;
    title?: string;
    description?: string;
    confirmText?: string;
    cancelText?: string;
    destructive?: boolean;
}>();

const emit = defineEmits<{
    confirm: [];
    cancel: [];
}>();
</script>

<template>
    <AlertDialog :open="open">
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>{{
                    title ?? 'Are you sure?'
                }}</AlertDialogTitle>
                <AlertDialogDescription>
                    {{ description ?? 'This action cannot be undone.' }}
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel @click="emit('cancel')">
                    {{ cancelText ?? 'Cancel' }}
                </AlertDialogCancel>
                <AlertDialogAction
                    :class="
                        destructive
                            ? 'bg-destructive text-destructive-foreground hover:bg-destructive/90'
                            : ''
                    "
                    @click="emit('confirm')"
                >
                    {{ confirmText ?? 'Continue' }}
                </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>
