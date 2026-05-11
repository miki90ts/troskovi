<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import FormInputField from '@/components/forms/FormInputField.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import { t } from '@/lib/i18n';
import { login } from '@/routes';
import { store } from '@/routes/register';
</script>

<template>
    <AuthBase
        :eyebrow="t('auth.register.eyebrow')"
        :title="t('auth.register.title')"
        :description="t('auth.register.description')"
        :panel-badge="t('auth.register.panelBadge')"
        :panel-title="t('auth.register.panelTitle')"
        :panel-description="t('auth.register.panelDescription')"
        :panel-stats="[
            { value: '1 min', label: t('auth.register.stats.setup') },
            { value: 'Jasno', label: t('auth.register.stats.overview') },
            { value: 'Jednostavno', label: t('auth.register.stats.recurring') },
        ]"
    >
        <Head :title="t('auth.register.head')" />

        <Form
            v-bind="store.form()"
            :reset-on-success="['password', 'password_confirmation']"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-7"
        >
            <div class="grid gap-5">
                <FormInputField
                    :label="t('auth.register.name')"
                    field-id="name"
                    type="text"
                    required
                    autofocus
                    :tabindex="1"
                    autocomplete="name"
                    name="name"
                    :placeholder="t('auth.register.name')"
                    :error="errors.name"
                />

                <FormInputField
                    :label="t('auth.register.email')"
                    field-id="email"
                    type="email"
                    required
                    :tabindex="2"
                    autocomplete="email"
                    name="email"
                    placeholder="email@example.com"
                    :error="errors.email"
                />

                <FormInputField
                    :component="PasswordInput"
                    :label="t('auth.register.password')"
                    field-id="password"
                    required
                    :tabindex="3"
                    autocomplete="new-password"
                    name="password"
                    :placeholder="t('auth.register.password')"
                    :error="errors.password"
                />

                <FormInputField
                    :component="PasswordInput"
                    :label="t('auth.register.confirmPassword')"
                    field-id="password_confirmation"
                    required
                    :tabindex="4"
                    autocomplete="new-password"
                    name="password_confirmation"
                    :placeholder="t('auth.register.confirmPassword')"
                    :error="errors.password_confirmation"
                />

                <Button
                    type="submit"
                    class="mt-2 h-12 w-full rounded-2xl text-sm font-semibold shadow-[0_18px_40px_rgba(13,148,136,0.25)]"
                    tabindex="5"
                    :disabled="processing"
                    data-test="register-user-button"
                >
                    <Spinner v-if="processing" />
                    {{ t('auth.register.submit') }}
                </Button>
            </div>

            <div
                class="rounded-2xl border border-border/70 bg-muted/35 px-4 py-4 text-center text-sm text-muted-foreground"
            >
                {{ t('auth.register.alreadyHaveAccount') }}
                <div class="mt-2">
                    <TextLink
                        :href="login()"
                        class="text-primary"
                        :tabindex="6"
                        >{{ t('auth.register.loginLink') }}</TextLink
                    >
                </div>
            </div>
        </Form>
    </AuthBase>
</template>
