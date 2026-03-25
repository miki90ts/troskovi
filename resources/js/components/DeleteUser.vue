<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { useTemplateRef } from 'vue';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { t } from '@/lib/i18n';

const passwordInput = useTemplateRef('passwordInput');
</script>

<template>
    <div class="space-y-6">
        <Heading
            variant="small"
            :title="t('settings.deleteAccount.title')"
            :description="t('settings.deleteAccount.description')"
        />
        <div
            class="space-y-4 rounded-3xl border border-red-200/70 bg-[linear-gradient(180deg,rgba(254,242,242,1),rgba(255,255,255,0.92))] p-5 dark:border-red-200/10 dark:bg-[linear-gradient(180deg,rgba(127,29,29,0.18),rgba(15,23,42,0.6))]"
        >
            <div class="relative space-y-0.5 text-red-600 dark:text-red-100">
                <p class="font-medium">
                    {{ t('settings.deleteAccount.warning') }}
                </p>
                <p class="text-sm">
                    {{ t('settings.deleteAccount.warningText') }}
                </p>
            </div>
            <Dialog>
                <DialogTrigger as-child>
                    <Button
                        variant="destructive"
                        class="rounded-2xl px-5 text-sm font-semibold"
                        data-test="delete-user-button"
                        >{{ t('settings.deleteAccount.trigger') }}</Button
                    >
                </DialogTrigger>
                <DialogContent>
                    <Form
                        v-bind="ProfileController.destroy.form()"
                        reset-on-success
                        @error="() => passwordInput?.focus()"
                        :options="{
                            preserveScroll: true,
                        }"
                        class="space-y-6"
                        v-slot="{ errors, processing, reset, clearErrors }"
                    >
                        <DialogHeader class="space-y-3">
                            <DialogTitle>{{
                                t('settings.deleteAccount.dialogTitle')
                            }}</DialogTitle>
                            <DialogDescription>
                                {{
                                    t(
                                        'settings.deleteAccount.dialogDescription',
                                    )
                                }}
                            </DialogDescription>
                        </DialogHeader>

                        <div class="grid gap-2.5">
                            <Label for="password" class="sr-only">{{
                                t('settings.deleteAccount.password')
                            }}</Label>
                            <PasswordInput
                                id="password"
                                name="password"
                                ref="passwordInput"
                                :placeholder="
                                    t('settings.deleteAccount.password')
                                "
                                class="h-12 rounded-2xl border-border/80 bg-background/70 px-4 shadow-none"
                            />
                            <InputError :message="errors.password" />
                        </div>

                        <DialogFooter class="gap-2">
                            <DialogClose as-child>
                                <Button
                                    variant="secondary"
                                    class="rounded-2xl"
                                    @click="
                                        () => {
                                            clearErrors();
                                            reset();
                                        }
                                    "
                                >
                                    {{ t('settings.common.cancel') }}
                                </Button>
                            </DialogClose>

                            <Button
                                type="submit"
                                variant="destructive"
                                class="rounded-2xl"
                                :disabled="processing"
                                data-test="confirm-delete-user-button"
                            >
                                {{ t('settings.deleteAccount.confirm') }}
                            </Button>
                        </DialogFooter>
                    </Form>
                </DialogContent>
            </Dialog>
        </div>
    </div>
</template>
