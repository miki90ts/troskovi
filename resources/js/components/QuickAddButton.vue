<script setup lang="ts">
import { Plus } from 'lucide-vue-next';
import { ref } from 'vue';
import TransactionFormDialog from '@/components/TransactionFormDialog.vue';

import type { Category } from '@/types/models';

defineProps<{
    categories: Category[];
    accounts: { id: number; name: string }[];
}>();

const emit = defineEmits<{ created: [] }>();
const showDialog = ref(false);
</script>

<template>
    <div>
        <button
            class="fixed right-6 bottom-6 z-50 flex h-14 w-14 items-center justify-center rounded-full bg-primary text-primary-foreground shadow-lg transition-transform hover:scale-105"
            @click="showDialog = true"
        >
            <Plus class="h-6 w-6" />
        </button>

        <TransactionFormDialog
            :open="showDialog"
            :categories="categories"
            :accounts="accounts"
            @close="showDialog = false"
            @saved="
                showDialog = false;
                emit('created');
            "
        />
    </div>
</template>
