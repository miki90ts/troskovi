<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { t } from '@/lib/i18n';
import { login } from '@/routes';
import { email } from '@/routes/password';

defineProps<{
    status?: string;
}>();
</script>

<template>
    <AuthLayout
        :eyebrow="t('auth.forgotPassword.eyebrow')"
        :title="t('auth.forgotPassword.title')"
        :description="t('auth.forgotPassword.description')"
        :panel-badge="t('auth.forgotPassword.panelBadge')"
        :panel-title="t('auth.forgotPassword.panelTitle')"
        :panel-description="t('auth.forgotPassword.panelDescription')"
        :panel-stats="[
            { value: 'Bezbedno', label: t('auth.forgotPassword.stats.link') },
            { value: 'Brzo', label: t('auth.forgotPassword.stats.fast') },
            { value: 'Jasno', label: t('auth.forgotPassword.stats.clear') },
        ]"
    >
        <Head :title="t('auth.forgotPassword.head')" />

        <div
            v-if="status"
            class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300"
        >
            {{ status }}
        </div>

        <div class="space-y-6">
            <Form
                v-bind="email.form()"
                v-slot="{ errors, processing }"
                class="space-y-6"
            >
                <div class="grid gap-2.5">
                    <Label
                        for="email"
                        class="text-sm font-medium text-foreground/90"
                        >{{ t('auth.forgotPassword.email') }}</Label
                    >
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        autocomplete="off"
                        autofocus
                        placeholder="email@example.com"
                        class="h-12 rounded-2xl border-border/80 bg-background/70 px-4 shadow-none"
                    />
                    <InputError :message="errors.email" />
                </div>

                <div class="flex items-center justify-start">
                    <Button
                        class="h-12 w-full rounded-2xl text-sm font-semibold shadow-[0_18px_40px_rgba(13,148,136,0.25)]"
                        :disabled="processing"
                        data-test="email-password-reset-link-button"
                    >
                        <Spinner v-if="processing" />
                        {{ t('auth.forgotPassword.submit') }}
                    </Button>
                </div>
            </Form>

            <div
                class="rounded-2xl border border-border/70 bg-muted/35 px-4 py-4 text-center text-sm text-muted-foreground"
            >
                {{ t('auth.forgotPassword.manualPrompt') }}
                <div class="mt-2">
                    <TextLink :href="login()">{{
                        t('auth.forgotPassword.loginLink')
                    }}</TextLink>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>
