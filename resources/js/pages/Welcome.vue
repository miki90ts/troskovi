<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { t } from '@/lib/i18n';
import { dashboard, register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    {
        canRegister: true,
    },
);
</script>

<template>
    <Head :title="t('auth.welcome.head')" />

    <div
        class="min-h-svh bg-[radial-gradient(circle_at_top_left,rgba(20,184,166,0.18),transparent_35%),radial-gradient(circle_at_bottom_right,rgba(45,212,191,0.18),transparent_28%),linear-gradient(180deg,hsl(168_30%_97%)_0%,hsl(0_0%_100%)_45%,hsl(166_28%_96%)_100%)] p-4 md:p-6 dark:bg-[radial-gradient(circle_at_top_left,rgba(20,184,166,0.18),transparent_32%),radial-gradient(circle_at_bottom_right,rgba(13,148,136,0.16),transparent_24%),linear-gradient(180deg,hsl(176_26%_12%)_0%,hsl(176_24%_9%)_100%)]"
    >
        <div
            class="mx-auto grid min-h-[calc(100svh-2rem)] w-full max-w-7xl overflow-hidden rounded-4xl border border-white/70 bg-white/75 shadow-[0_24px_80px_rgba(15,23,42,0.12)] backdrop-blur xl:grid-cols-[1.08fr_0.92fr] dark:border-white/10 dark:bg-slate-950/70 dark:shadow-[0_30px_80px_rgba(0,0,0,0.45)]"
        >
            <section
                class="relative hidden overflow-hidden xl:flex xl:flex-col xl:justify-between"
            >
                <div
                    class="absolute inset-0 bg-[linear-gradient(160deg,rgba(15,118,110,0.98),rgba(6,78,59,0.95))]"
                />
                <div
                    class="absolute inset-0 bg-[radial-gradient(circle_at_18%_20%,rgba(153,246,228,0.22),transparent_22%),radial-gradient(circle_at_85%_18%,rgba(255,255,255,0.16),transparent_18%),radial-gradient(circle_at_50%_100%,rgba(45,212,191,0.26),transparent_30%)]"
                />
                <div
                    class="absolute top-28 left-12 h-64 w-64 rounded-full border border-white/10 bg-white/8 blur-3xl"
                />
                <div
                    class="absolute right-6 bottom-8 h-72 w-72 rounded-full border border-white/10 bg-emerald-300/10 blur-3xl"
                />

                <div
                    class="relative z-10 flex items-center justify-between px-10 pt-10"
                >
                    <div class="inline-flex items-center gap-3 text-white">
                        <div
                            class="flex size-12 items-center justify-center rounded-2xl bg-white/14 ring-1 ring-white/15 backdrop-blur-sm"
                        >
                            <AppLogoIcon
                                class="size-7 fill-current text-white"
                            />
                        </div>
                        <div>
                            <p
                                class="text-sm font-medium tracking-[0.28em] text-teal-100/75 uppercase"
                            >
                                Troskovi
                            </p>
                            <p class="text-base font-semibold text-white">
                                {{ t('auth.welcome.workspace') }}
                            </p>
                        </div>
                    </div>

                    <Link
                        v-if="$page.props.auth.user"
                        :href="dashboard()"
                        class="rounded-full border border-white/15 bg-white/10 px-5 py-2.5 text-sm font-medium text-white backdrop-blur-sm transition hover:bg-white/16"
                    >
                        {{ t('auth.welcome.dashboard') }}
                    </Link>
                    <span
                        v-else
                        class="rounded-full border border-white/15 bg-white/10 px-4 py-2 text-xs font-semibold tracking-[0.22em] text-teal-50 uppercase backdrop-blur-sm"
                    >
                        {{ t('auth.welcome.loginFirst') }}
                    </span>
                </div>

                <div
                    class="relative z-10 flex flex-1 flex-col justify-center px-10 py-12"
                >
                    <div class="max-w-xl space-y-7 text-white">
                        <p
                            class="text-sm font-semibold tracking-[0.35em] text-teal-100/80 uppercase"
                        >
                            {{ t('auth.welcome.smarterCashFlow') }}
                        </p>
                        <h1
                            class="max-w-2xl text-5xl leading-tight font-semibold tracking-[-0.05em] text-white"
                        >
                            {{ t('auth.welcome.heroTitle') }}
                        </h1>
                        <p class="max-w-lg text-lg leading-8 text-teal-50/84">
                            {{ t('auth.welcome.heroDescription') }}
                        </p>

                        <div class="grid gap-4 sm:grid-cols-3">
                            <div
                                class="rounded-3xl border border-white/14 bg-white/10 p-5 backdrop-blur-sm"
                            >
                                <p
                                    class="text-2xl font-semibold tracking-[-0.03em] text-white"
                                >
                                    {{ t('auth.welcome.cards.allInOne') }}
                                </p>
                                <p class="mt-1 text-sm text-teal-50/74">
                                    {{
                                        t(
                                            'auth.welcome.cards.allInOneDescription',
                                        )
                                    }}
                                </p>
                            </div>
                            <div
                                class="rounded-3xl border border-white/14 bg-white/10 p-5 backdrop-blur-sm"
                            >
                                <p
                                    class="text-2xl font-semibold tracking-[-0.03em] text-white"
                                >
                                    {{ t('auth.welcome.cards.recurring') }}
                                </p>
                                <p class="mt-1 text-sm text-teal-50/74">
                                    {{
                                        t(
                                            'auth.welcome.cards.recurringDescription',
                                        )
                                    }}
                                </p>
                            </div>
                            <div
                                class="rounded-3xl border border-white/14 bg-white/10 p-5 backdrop-blur-sm"
                            >
                                <p
                                    class="text-2xl font-semibold tracking-[-0.03em] text-white"
                                >
                                    {{ t('auth.welcome.cards.reports') }}
                                </p>
                                <p class="mt-1 text-sm text-teal-50/74">
                                    {{
                                        t(
                                            'auth.welcome.cards.reportsDescription',
                                        )
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="relative z-10 px-10 pb-10">
                    <div
                        class="grid gap-4 rounded-[1.75rem] border border-white/14 bg-white/10 p-6 backdrop-blur-sm sm:grid-cols-[0.86fr_1.14fr]"
                    >
                        <div>
                            <p
                                class="text-xs font-semibold tracking-[0.3em] text-teal-100/80 uppercase"
                            >
                                {{ t('auth.welcome.clarity.badge') }}
                            </p>
                            <p
                                class="mt-3 text-2xl font-semibold tracking-[-0.04em] text-white"
                            >
                                {{ t('auth.welcome.clarity.title') }}
                            </p>
                        </div>
                        <p class="text-sm leading-7 text-teal-50/80">
                            {{ t('auth.welcome.clarity.description') }}
                        </p>
                    </div>
                </div>
            </section>

            <section
                class="relative flex min-h-full items-center justify-center px-5 py-8 sm:px-8 lg:px-12"
            >
                <div class="w-full max-w-lg">
                    <div
                        class="rounded-[1.75rem] border border-white/80 bg-white/86 p-6 shadow-[0_20px_60px_rgba(15,23,42,0.08)] backdrop-blur sm:p-8 dark:border-white/10 dark:bg-slate-950/82 dark:shadow-none"
                    >
                        <div class="mb-8 flex flex-col gap-6">
                            <div
                                class="flex items-center justify-between xl:hidden"
                            >
                                <div
                                    class="inline-flex items-center gap-3 text-foreground"
                                >
                                    <div
                                        class="flex size-11 items-center justify-center rounded-2xl bg-primary/10 text-primary ring-1 ring-primary/15"
                                    >
                                        <AppLogoIcon
                                            class="size-6 fill-current"
                                        />
                                    </div>
                                    <div>
                                        <p
                                            class="text-xs font-semibold tracking-[0.24em] text-muted-foreground uppercase"
                                        >
                                            Troskovi
                                        </p>
                                        <p
                                            class="text-sm font-semibold text-foreground"
                                        >
                                            {{
                                                t(
                                                    'auth.welcome.mobile.subtitle',
                                                )
                                            }}
                                        </p>
                                    </div>
                                </div>
                                <span
                                    class="rounded-full bg-primary/10 px-3 py-1 text-[11px] font-semibold tracking-[0.2em] text-primary uppercase"
                                >
                                    {{ t('auth.welcome.mobile.secure') }}
                                </span>
                            </div>

                            <div class="space-y-3">
                                <p
                                    class="text-sm font-semibold tracking-[0.32em] text-primary/80 uppercase"
                                >
                                    {{ t('auth.login.eyebrow') }}
                                </p>
                                <h2
                                    class="text-3xl font-semibold tracking-[-0.04em] text-foreground sm:text-4xl"
                                >
                                    {{ t('auth.welcome.loginTitle') }}
                                </h2>
                                <p
                                    class="max-w-md text-sm leading-7 text-muted-foreground sm:text-base"
                                >
                                    {{ t('auth.welcome.loginDescription') }}
                                </p>
                            </div>
                        </div>

                        <Form
                            v-if="!$page.props.auth.user"
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
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <Label
                                            for="password"
                                            class="text-sm font-medium text-foreground/90"
                                            >{{
                                                t('auth.login.password')
                                            }}</Label
                                        >
                                        <TextLink
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
                                        <Checkbox
                                            id="remember"
                                            name="remember"
                                            :tabindex="3"
                                        />
                                        <span>{{
                                            t('auth.login.rememberMe')
                                        }}</span>
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
                                    <span>{{
                                        t('auth.welcome.getStarted')
                                    }}</span>
                                    <span class="h-px flex-1 bg-border" />
                                </div>

                                <div
                                    v-if="canRegister"
                                    class="rounded-2xl border border-border/70 bg-muted/35 px-4 py-4 text-center text-sm text-muted-foreground"
                                >
                                    {{ t('auth.welcome.registerPrompt') }}
                                    <div class="mt-2">
                                        <TextLink :href="register()">{{
                                            t('auth.login.registerLink')
                                        }}</TextLink>
                                    </div>
                                </div>
                            </div>
                        </Form>

                        <div v-else class="space-y-5">
                            <div
                                class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-4 text-sm leading-7 text-emerald-700 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300"
                            >
                                {{ t('auth.welcome.loggedIn') }}
                            </div>
                            <Button
                                as-child
                                class="h-12 w-full rounded-2xl text-sm font-semibold shadow-[0_18px_40px_rgba(13,148,136,0.25)]"
                            >
                                <Link :href="dashboard()">{{
                                    t('auth.welcome.openDashboard')
                                }}</Link>
                            </Button>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>
