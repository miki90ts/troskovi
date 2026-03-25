<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    InputOTP,
    InputOTPGroup,
    InputOTPSlot,
} from '@/components/ui/input-otp';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { t } from '@/lib/i18n';
import { store } from '@/routes/two-factor/login';
import type { TwoFactorConfigContent } from '@/types';

const authConfigContent = computed<TwoFactorConfigContent>(() => {
    if (showRecoveryInput.value) {
        return {
            title: t('auth.twoFactor.recoveryTitle'),
            description: t('auth.twoFactor.recoveryDescription'),
            buttonText: t('auth.twoFactor.recoveryToggle'),
        };
    }

    return {
        title: t('auth.twoFactor.authTitle'),
        description: t('auth.twoFactor.authDescription'),
        buttonText: t('auth.twoFactor.authToggle'),
    };
});

const showRecoveryInput = ref<boolean>(false);

const toggleRecoveryMode = (clearErrors: () => void): void => {
    showRecoveryInput.value = !showRecoveryInput.value;
    clearErrors();
    code.value = '';
};

const code = ref<string>('');
</script>

<template>
    <AuthLayout
        :eyebrow="t('auth.twoFactor.eyebrow')"
        :title="authConfigContent.title"
        :description="authConfigContent.description"
        :panel-badge="t('auth.twoFactor.panelBadge')"
        :panel-title="t('auth.twoFactor.panelTitle')"
        :panel-description="t('auth.twoFactor.panelDescription')"
        :panel-stats="[
            { value: '2FA', label: t('auth.twoFactor.stats.app') },
            { value: 'Backup', label: t('auth.twoFactor.stats.backup') },
            { value: 'Bezbedno', label: t('auth.twoFactor.stats.secure') },
        ]"
    >
        <Head :title="t('auth.twoFactor.head')" />

        <div class="space-y-6">
            <template v-if="!showRecoveryInput">
                <Form
                    v-bind="store.form()"
                    class="space-y-5"
                    reset-on-error
                    @error="code = ''"
                    #default="{ errors, processing, clearErrors }"
                >
                    <input type="hidden" name="code" :value="code" />
                    <div
                        class="flex flex-col items-center justify-center space-y-4 rounded-3xl border border-border/70 bg-muted/25 px-4 py-5 text-center"
                    >
                        <div class="flex w-full items-center justify-center">
                            <InputOTP
                                id="otp"
                                v-model="code"
                                :maxlength="6"
                                :disabled="processing"
                                autofocus
                            >
                                <InputOTPGroup>
                                    <InputOTPSlot
                                        v-for="index in 6"
                                        :key="index"
                                        :index="index - 1"
                                    />
                                </InputOTPGroup>
                            </InputOTP>
                        </div>
                        <InputError :message="errors.code" />
                    </div>
                    <Button
                        type="submit"
                        class="h-12 w-full rounded-2xl text-sm font-semibold shadow-[0_18px_40px_rgba(13,148,136,0.25)]"
                        :disabled="processing"
                        >{{ t('auth.twoFactor.continue') }}</Button
                    >
                    <div
                        class="rounded-2xl border border-border/70 bg-muted/35 px-4 py-4 text-center text-sm text-muted-foreground"
                    >
                        <span>{{ t('auth.twoFactor.alternate') }} </span>
                        <button
                            type="button"
                            class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                            @click="() => toggleRecoveryMode(clearErrors)"
                        >
                            {{ authConfigContent.buttonText }}
                        </button>
                    </div>
                </Form>
            </template>

            <template v-else>
                <Form
                    v-bind="store.form()"
                    class="space-y-5"
                    reset-on-error
                    #default="{ errors, processing, clearErrors }"
                >
                    <Input
                        name="recovery_code"
                        type="text"
                        :placeholder="t('auth.twoFactor.recoveryPlaceholder')"
                        :autofocus="showRecoveryInput"
                        required
                        class="h-12 rounded-2xl border-border/80 bg-background/70 px-4 shadow-none"
                    />
                    <InputError :message="errors.recovery_code" />
                    <Button
                        type="submit"
                        class="h-12 w-full rounded-2xl text-sm font-semibold shadow-[0_18px_40px_rgba(13,148,136,0.25)]"
                        :disabled="processing"
                        >{{ t('auth.twoFactor.continue') }}</Button
                    >

                    <div
                        class="rounded-2xl border border-border/70 bg-muted/35 px-4 py-4 text-center text-sm text-muted-foreground"
                    >
                        <span>{{ t('auth.twoFactor.alternate') }} </span>
                        <button
                            type="button"
                            class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                            @click="() => toggleRecoveryMode(clearErrors)"
                        >
                            {{ authConfigContent.buttonText }}
                        </button>
                    </div>
                </Form>
            </template>
        </div>
    </AuthLayout>
</template>
