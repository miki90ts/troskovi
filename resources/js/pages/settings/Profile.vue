<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import DeleteUser from '@/components/DeleteUser.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { t } from '@/lib/i18n';
import { edit } from '@/routes/profile';
import { send } from '@/routes/verification';
import type { BreadcrumbItem } from '@/types';

type Props = {
    mustVerifyEmail: boolean;
    status?: string;
};

defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: t('settings.profile.head'),
        href: edit(),
    },
];

const page = usePage();
const user = computed(() => page.props.auth.user);
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="t('settings.profile.head')" />

        <h1 class="sr-only">{{ t('settings.profile.head') }}</h1>

        <SettingsLayout>
            <div
                class="flex flex-col space-y-6 rounded-[1.75rem] border border-border/70 bg-card/95 p-6 shadow-[0_18px_50px_rgba(15,23,42,0.06)] sm:p-8"
            >
                <Heading
                    variant="small"
                    :title="t('settings.profile.title')"
                    :description="t('settings.profile.description')"
                />

                <Form
                    v-bind="ProfileController.update.form()"
                    class="space-y-6"
                    v-slot="{ errors, processing, recentlySuccessful }"
                >
                    <div class="grid gap-2.5">
                        <Label for="name">{{
                            t('settings.profile.name')
                        }}</Label>
                        <Input
                            id="name"
                            class="h-12 rounded-2xl border-border/80 bg-background/70 px-4 shadow-none"
                            name="name"
                            :default-value="user.name"
                            required
                            autocomplete="name"
                            :placeholder="t('settings.profile.fullName')"
                        />
                        <InputError :message="errors.name" />
                    </div>

                    <div class="grid gap-2.5">
                        <Label for="email">{{
                            t('settings.profile.email')
                        }}</Label>
                        <Input
                            id="email"
                            type="email"
                            class="h-12 rounded-2xl border-border/80 bg-background/70 px-4 shadow-none"
                            name="email"
                            :default-value="user.email"
                            required
                            autocomplete="username"
                            :placeholder="
                                t('settings.profile.emailPlaceholder')
                            "
                        />
                        <InputError :message="errors.email" />
                    </div>

                    <div v-if="mustVerifyEmail && !user.email_verified_at">
                        <p
                            class="rounded-2xl border border-border/70 bg-muted/35 px-4 py-4 text-sm leading-7 text-muted-foreground"
                        >
                            {{ t('settings.profile.emailUnverified') }}
                            <Link
                                :href="send()"
                                as="button"
                                class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                            >
                                {{ t('settings.profile.resend') }}
                            </Link>
                        </p>

                        <div
                            v-if="status === 'verification-link-sent'"
                            class="mt-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300"
                        >
                            {{ t('settings.profile.linkSent') }}
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <Button
                            class="h-11 rounded-2xl px-5 text-sm font-semibold shadow-[0_18px_40px_rgba(13,148,136,0.18)]"
                            :disabled="processing"
                            data-test="update-profile-button"
                            >{{ t('settings.common.save') }}</Button
                        >

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

            <DeleteUser />
        </SettingsLayout>
    </AppLayout>
</template>
