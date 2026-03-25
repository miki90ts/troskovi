<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import { t } from '@/lib/i18n';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}>();
</script>

<template>
    <AuthBase
        :eyebrow="t('auth.login.eyebrow')"
        :title="t('auth.login.title')"
        :description="t('auth.login.description')"
        :panel-badge="t('auth.login.panelBadge')"
        :panel-title="t('auth.login.panelTitle')"
        :panel-description="t('auth.login.panelDescription')"
        :panel-stats="[
            { value: '24/7', label: t('auth.login.stats.balances') },
            { value: 'Pametno', label: t('auth.login.stats.categories') },
            { value: 'Brzo', label: t('auth.login.stats.capture') },
        ]"
    >
        <Head :title="t('auth.login.head')" />

        <div
            v-if="status"
            class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300"
        >
            {{ status }}
        </div>

        <Form
            v-bind="store.form()"
            :reset-on-success="['password']"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-7"
        >
            <div class="grid gap-5">
                <div class="grid gap-2.5">
                    <Label
                        for="email"
                        class="text-sm font-medium text-foreground/90"
                        >{{ t('auth.login.email') }}</Label
                    >
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="email"
                        placeholder="email@example.com"
                        class="h-12 rounded-2xl border-border/80 bg-background/70 px-4 shadow-none"
                    />
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-2.5">
                    <div class="flex items-center justify-between">
                        <Label
                            for="password"
                            class="text-sm font-medium text-foreground/90"
                            >{{ t('auth.login.password') }}</Label
                        >
                        <TextLink
                            v-if="canResetPassword"
                            :href="request()"
                            class="text-sm text-primary"
                            :tabindex="5"
                        >
                            {{ t('auth.login.forgotPassword') }}
                        </TextLink>
                    </div>
                    <PasswordInput
                        id="password"
                        name="password"
                        required
                        :tabindex="2"
                        autocomplete="current-password"
                        :placeholder="t('auth.login.password')"
                        class="h-12 rounded-2xl border-border/80 bg-background/70 px-4 shadow-none"
                    />
                    <InputError :message="errors.password" />
                </div>

                <div
                    class="flex items-center justify-between rounded-2xl border border-border/70 bg-muted/40 px-4 py-3"
                >
                    <Label
                        for="remember"
                        class="flex items-center space-x-3 text-sm text-foreground/90"
                    >
                        <Checkbox id="remember" name="remember" :tabindex="3" />
                        <span>{{ t('auth.login.rememberMe') }}</span>
                    </Label>
                    <span
                        class="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase"
                    >
                        {{ t('auth.login.privateSession') }}
                    </span>
                </div>

                <Button
                    type="submit"
                    class="mt-2 h-12 w-full rounded-2xl text-sm font-semibold shadow-[0_18px_40px_rgba(13,148,136,0.25)]"
                    :tabindex="4"
                    :disabled="processing"
                    data-test="login-button"
                >
                    <Spinner v-if="processing" />
                    {{ t('auth.login.submit') }}
                </Button>
            </div>

            <div class="space-y-4">
                <div
                    class="flex items-center gap-3 text-xs font-medium tracking-[0.28em] text-muted-foreground uppercase"
                >
                    <span class="h-px flex-1 bg-border" />
                    <span>{{ t('auth.login.newHere') }}</span>
                    <span class="h-px flex-1 bg-border" />
                </div>

                <div
                    v-if="canRegister"
                    class="rounded-2xl border border-border/70 bg-muted/35 px-4 py-4 text-center text-sm text-muted-foreground"
                >
                    {{ t('auth.login.registerPrompt') }}
                    <div class="mt-2">
                        <TextLink :href="register()" :tabindex="5">{{
                            t('auth.login.registerLink')
                        }}</TextLink>
                    </div>
                </div>
            </div>
        </Form>
    </AuthBase>
</template>
