<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { t } from '@/lib/i18n';
import { logout } from '@/routes';
import { send } from '@/routes/verification';

defineProps<{
    status?: string;
}>();
</script>

<template>
    <AuthLayout
        :eyebrow="t('auth.verifyEmail.eyebrow')"
        :title="t('auth.verifyEmail.title')"
        :description="t('auth.verifyEmail.description')"
        :panel-badge="t('auth.verifyEmail.panelBadge')"
        :panel-title="t('auth.verifyEmail.panelTitle')"
        :panel-description="t('auth.verifyEmail.panelDescription')"
        :panel-stats="[
            { value: 'Bezbedno', label: t('auth.verifyEmail.stats.secure') },
            { value: 'Brzo', label: t('auth.verifyEmail.stats.quick') },
            { value: 'Spremno', label: t('auth.verifyEmail.stats.ready') },
        ]"
    >
        <Head :title="t('auth.verifyEmail.head')" />

        <div
            v-if="status === 'verification-link-sent'"
            class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300"
        >
            {{ t('auth.verifyEmail.sentMessage') }}
        </div>

        <Form
            v-bind="send.form()"
            class="space-y-6 text-center"
            v-slot="{ processing }"
        >
            <Button
                :disabled="processing"
                class="h-12 w-full rounded-2xl text-sm font-semibold shadow-[0_18px_40px_rgba(13,148,136,0.16)]"
            >
                <Spinner v-if="processing" />
                {{ t('auth.verifyEmail.submit') }}
            </Button>

            <div
                class="rounded-2xl border border-border/70 bg-muted/35 px-4 py-4 text-sm text-muted-foreground"
            >
                {{ t('auth.verifyEmail.logoutPrompt') }}
                <div class="mt-2">
                    <TextLink
                        :href="logout()"
                        as="button"
                        class="mx-auto block text-sm"
                    >
                        {{ t('auth.verifyEmail.logout') }}
                    </TextLink>
                </div>
            </div>
        </Form>
    </AuthLayout>
</template>
