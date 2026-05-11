<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import FormInputField from '@/components/forms/FormInputField.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { t } from '@/lib/i18n';
import { update } from '@/routes/password';

const props = defineProps<{
    token: string;
    email: string;
}>();

const inputEmail = ref(props.email);
</script>

<template>
    <AuthLayout
        :eyebrow="t('auth.resetPassword.eyebrow')"
        :title="t('auth.resetPassword.title')"
        :description="t('auth.resetPassword.description')"
        :panel-badge="t('auth.resetPassword.panelBadge')"
        :panel-title="t('auth.resetPassword.panelTitle')"
        :panel-description="t('auth.resetPassword.panelDescription')"
        :panel-stats="[
            {
                value: 'Zasticeno',
                label: t('auth.resetPassword.stats.protected'),
            },
            { value: 'Jako', label: t('auth.resetPassword.stats.strong') },
            {
                value: 'Brz povratak',
                label: t('auth.resetPassword.stats.back'),
            },
        ]"
    >
        <Head :title="t('auth.resetPassword.head')" />

        <Form
            v-bind="update.form()"
            :transform="(data) => ({ ...data, token, email })"
            :reset-on-success="['password', 'password_confirmation']"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-7"
        >
            <div class="grid gap-5">
                <FormInputField
                    :label="t('auth.resetPassword.email')"
                    field-id="email"
                    type="email"
                    name="email"
                    autocomplete="email"
                    v-model="inputEmail"
                    class="h-12 rounded-2xl border-border/80 bg-muted/35 px-4 shadow-none"
                    readonly
                    :error="errors.email"
                />

                <FormInputField
                    :component="PasswordInput"
                    :label="t('auth.resetPassword.password')"
                    field-id="password"
                    name="password"
                    autocomplete="new-password"
                    autofocus
                    :placeholder="t('auth.resetPassword.password')"
                    :error="errors.password"
                />

                <FormInputField
                    :component="PasswordInput"
                    :label="t('auth.resetPassword.confirmPassword')"
                    field-id="password_confirmation"
                    name="password_confirmation"
                    autocomplete="new-password"
                    :placeholder="t('auth.resetPassword.confirmPassword')"
                    :error="errors.password_confirmation"
                />

                <Button
                    type="submit"
                    class="mt-2 h-12 w-full rounded-2xl text-sm font-semibold shadow-[0_18px_40px_rgba(13,148,136,0.25)]"
                    :disabled="processing"
                    data-test="reset-password-button"
                >
                    <Spinner v-if="processing" />
                    {{ t('auth.resetPassword.submit') }}
                </Button>
            </div>
        </Form>
    </AuthLayout>
</template>
