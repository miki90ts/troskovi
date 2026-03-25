<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { ShieldCheck } from 'lucide-vue-next';
import { onUnmounted, ref } from 'vue';
import SecurityController from '@/actions/App/Http/Controllers/Settings/SecurityController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TwoFactorRecoveryCodes from '@/components/TwoFactorRecoveryCodes.vue';
import TwoFactorSetupModal from '@/components/TwoFactorSetupModal.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { useTwoFactorAuth } from '@/composables/useTwoFactorAuth';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { t } from '@/lib/i18n';
import { edit } from '@/routes/security';
import { disable, enable } from '@/routes/two-factor';
import type { BreadcrumbItem } from '@/types';

type Props = {
    canManageTwoFactor?: boolean;
    requiresConfirmation?: boolean;
    twoFactorEnabled?: boolean;
};

withDefaults(defineProps<Props>(), {
    canManageTwoFactor: false,
    requiresConfirmation: false,
    twoFactorEnabled: false,
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: t('settings.security.head'),
        href: edit(),
    },
];

const { hasSetupData, clearTwoFactorAuthData } = useTwoFactorAuth();
const showSetupModal = ref<boolean>(false);

onUnmounted(() => clearTwoFactorAuthData());
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="t('settings.security.head')" />

        <h1 class="sr-only">{{ t('settings.security.head') }}</h1>

        <SettingsLayout>
            <div
                class="space-y-6 rounded-[1.75rem] border border-border/70 bg-card/95 p-6 shadow-[0_18px_50px_rgba(15,23,42,0.06)] sm:p-8"
            >
                <Heading
                    variant="small"
                    :title="t('settings.security.title')"
                    :description="t('settings.security.description')"
                />

                <Form
                    v-bind="SecurityController.update.form()"
                    :options="{
                        preserveScroll: true,
                    }"
                    reset-on-success
                    :reset-on-error="[
                        'password',
                        'password_confirmation',
                        'current_password',
                    ]"
                    class="space-y-6"
                    v-slot="{ errors, processing, recentlySuccessful }"
                >
                    <div class="grid gap-2.5">
                        <Label for="current_password">{{
                            t('settings.security.currentPassword')
                        }}</Label>
                        <PasswordInput
                            id="current_password"
                            name="current_password"
                            class="h-12 rounded-2xl border-border/80 bg-background/70 px-4 shadow-none"
                            autocomplete="current-password"
                            :placeholder="
                                t('settings.security.currentPassword')
                            "
                        />
                        <InputError :message="errors.current_password" />
                    </div>

                    <div class="grid gap-2.5">
                        <Label for="password">{{
                            t('settings.security.newPassword')
                        }}</Label>
                        <PasswordInput
                            id="password"
                            name="password"
                            class="h-12 rounded-2xl border-border/80 bg-background/70 px-4 shadow-none"
                            autocomplete="new-password"
                            :placeholder="t('settings.security.newPassword')"
                        />
                        <InputError :message="errors.password" />
                    </div>

                    <div class="grid gap-2.5">
                        <Label for="password_confirmation">{{
                            t('settings.security.confirmPassword')
                        }}</Label>
                        <PasswordInput
                            id="password_confirmation"
                            name="password_confirmation"
                            class="h-12 rounded-2xl border-border/80 bg-background/70 px-4 shadow-none"
                            autocomplete="new-password"
                            :placeholder="
                                t('settings.security.confirmPassword')
                            "
                        />
                        <InputError :message="errors.password_confirmation" />
                    </div>

                    <div class="flex items-center gap-4">
                        <Button
                            class="h-11 rounded-2xl px-5 text-sm font-semibold shadow-[0_18px_40px_rgba(13,148,136,0.18)]"
                            :disabled="processing"
                            data-test="update-password-button"
                        >
                            {{ t('settings.security.savePassword') }}
                        </Button>

                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p
                                v-show="recentlySuccessful"
                                class="text-sm text-muted-foreground"
                            >
                                {{ t('settings.common.saved') }}
                            </p>
                        </Transition>
                    </div>
                </Form>
            </div>

            <div
                v-if="canManageTwoFactor"
                class="space-y-6 rounded-[1.75rem] border border-border/70 bg-card/95 p-6 shadow-[0_18px_50px_rgba(15,23,42,0.06)] sm:p-8"
            >
                <Heading
                    variant="small"
                    :title="t('settings.security.twoFactorTitle')"
                    :description="t('settings.security.twoFactorDescription')"
                />

                <div
                    v-if="!twoFactorEnabled"
                    class="flex flex-col items-start justify-start space-y-4"
                >
                    <p
                        class="rounded-2xl border border-border/70 bg-muted/35 px-4 py-4 text-sm leading-7 text-muted-foreground"
                    >
                        {{ t('settings.security.twoFactorIntro') }}
                    </p>

                    <div>
                        <Button
                            v-if="hasSetupData"
                            class="h-11 rounded-2xl px-5 text-sm font-semibold shadow-[0_18px_40px_rgba(13,148,136,0.18)]"
                            @click="showSetupModal = true"
                        >
                            <ShieldCheck />{{
                                t('settings.security.continueSetup')
                            }}
                        </Button>
                        <Form
                            v-else
                            v-bind="enable.form()"
                            @success="showSetupModal = true"
                            #default="{ processing }"
                        >
                            <Button
                                type="submit"
                                :disabled="processing"
                                class="h-11 rounded-2xl px-5 text-sm font-semibold shadow-[0_18px_40px_rgba(13,148,136,0.18)]"
                            >
                                {{ t('settings.security.enable2fa') }}
                            </Button>
                        </Form>
                    </div>
                </div>

                <div
                    v-else
                    class="flex flex-col items-start justify-start space-y-4"
                >
                    <p
                        class="rounded-2xl border border-border/70 bg-muted/35 px-4 py-4 text-sm leading-7 text-muted-foreground"
                    >
                        {{ t('settings.security.twoFactorEnabled') }}
                    </p>

                    <div class="relative inline">
                        <Form v-bind="disable.form()" #default="{ processing }">
                            <Button
                                variant="destructive"
                                type="submit"
                                :disabled="processing"
                                class="h-11 rounded-2xl px-5 text-sm font-semibold"
                            >
                                {{ t('settings.security.disable2fa') }}
                            </Button>
                        </Form>
                    </div>

                    <TwoFactorRecoveryCodes />
                </div>

                <TwoFactorSetupModal
                    v-model:isOpen="showSetupModal"
                    :requiresConfirmation="requiresConfirmation"
                    :twoFactorEnabled="twoFactorEnabled"
                />
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
