<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import FormInputField from '@/components/forms/FormInputField.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { t } from '@/lib/i18n';
import { store } from '@/routes/password/confirm';
</script>

<template>
    <AuthLayout
        :eyebrow="t('auth.confirmPassword.eyebrow')"
        :title="t('auth.confirmPassword.title')"
        :description="t('auth.confirmPassword.description')"
        :panel-badge="t('auth.confirmPassword.panelBadge')"
        :panel-title="t('auth.confirmPassword.panelTitle')"
        :panel-description="t('auth.confirmPassword.panelDescription')"
        :panel-stats="[
            { value: 'Bezbedno', label: t('auth.confirmPassword.stats.safe') },
            {
                value: 'Privatno',
                label: t('auth.confirmPassword.stats.private'),
            },
            { value: 'Brzo', label: t('auth.confirmPassword.stats.fast') },
        ]"
    >
        <Head :title="t('auth.confirmPassword.head')" />

        <Form
            v-bind="store.form()"
            reset-on-success
            v-slot="{ errors, processing }"
            class="flex flex-col gap-7"
        >
            <div class="space-y-6">
                <FormInputField
                    :component="PasswordInput"
                    :label="t('auth.confirmPassword.password')"
                    field-id="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    autofocus
                    :error="errors.password"
                />

                <div class="flex items-center">
                    <Button
                        class="h-12 w-full rounded-2xl text-sm font-semibold shadow-[0_18px_40px_rgba(13,148,136,0.25)]"
                        :disabled="processing"
                        data-test="confirm-password-button"
                    >
                        <Spinner v-if="processing" />
                        {{ t('auth.confirmPassword.submit') }}
                    </Button>
                </div>
            </div>
        </Form>
    </AuthLayout>
</template>
