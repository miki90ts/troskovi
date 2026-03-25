<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { home } from '@/routes';

const page = usePage();

defineProps<{
    title?: string;
    description?: string;
    eyebrow?: string;
    panelTitle?: string;
    panelDescription?: string;
    panelBadge?: string;
    panelStats?: Array<{ value: string; label: string }>;
}>();
</script>

<template>
    <div
        class="min-h-svh bg-[radial-gradient(circle_at_top_left,rgba(20,184,166,0.18),transparent_35%),radial-gradient(circle_at_bottom_right,rgba(45,212,191,0.2),transparent_30%),linear-gradient(180deg,hsl(168_30%_97%)_0%,hsl(0_0%_100%)_45%,hsl(166_28%_96%)_100%)] p-4 md:p-6 dark:bg-[radial-gradient(circle_at_top_left,rgba(20,184,166,0.18),transparent_32%),radial-gradient(circle_at_bottom_right,rgba(13,148,136,0.2),transparent_26%),linear-gradient(180deg,hsl(176_26%_12%)_0%,hsl(176_24%_9%)_100%)]"
    >
        <div
            class="mx-auto grid min-h-[calc(100svh-2rem)] w-full max-w-7xl overflow-hidden rounded-4xl border border-white/70 bg-white/75 shadow-[0_24px_80px_rgba(15,23,42,0.12)] backdrop-blur xl:grid-cols-[1.08fr_0.92fr] dark:border-white/10 dark:bg-slate-950/70 dark:shadow-[0_30px_80px_rgba(0,0,0,0.45)]"
        >
            <aside
                class="relative hidden overflow-hidden xl:flex xl:flex-col xl:justify-between"
            >
                <div
                    class="absolute inset-0 bg-[linear-gradient(160deg,rgba(15,118,110,0.96),rgba(6,78,59,0.94))]"
                />
                <div
                    class="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(153,246,228,0.22),transparent_22%),radial-gradient(circle_at_80%_32%,rgba(255,255,255,0.16),transparent_18%),radial-gradient(circle_at_50%_100%,rgba(45,212,191,0.24),transparent_30%)]"
                />
                <div
                    class="absolute top-28 -left-16 h-48 w-48 rounded-full border border-white/15 bg-white/10 blur-2xl"
                />
                <div
                    class="absolute right-10 bottom-12 h-56 w-56 rounded-full border border-white/10 bg-emerald-300/10 blur-3xl"
                />

                <div
                    class="relative z-10 flex items-center justify-between px-10 pt-10"
                >
                    <Link
                        :href="home()"
                        class="inline-flex items-center gap-3 text-white"
                    >
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
                                {{ page.props.name ?? 'Troskovi' }}
                            </p>
                            <p class="text-base font-semibold text-white">
                                Finance control hub
                            </p>
                        </div>
                    </Link>
                    <span
                        v-if="panelBadge"
                        class="rounded-full border border-white/15 bg-white/10 px-4 py-2 text-xs font-medium tracking-[0.2em] text-teal-50 uppercase backdrop-blur-sm"
                    >
                        {{ panelBadge }}
                    </span>
                </div>

                <div
                    class="relative z-10 flex flex-1 flex-col justify-center px-10 py-12"
                >
                    <slot name="aside">
                        <div class="max-w-xl space-y-6 text-white">
                            <p
                                v-if="eyebrow"
                                class="text-sm font-semibold tracking-[0.35em] text-teal-100/80 uppercase"
                            >
                                {{ eyebrow }}
                            </p>
                            <h2
                                class="max-w-lg text-5xl leading-tight font-semibold tracking-[-0.04em] text-white"
                            >
                                {{ panelTitle }}
                            </h2>
                            <p
                                class="max-w-lg text-lg leading-8 text-teal-50/84"
                            >
                                {{ panelDescription }}
                            </p>
                        </div>
                    </slot>
                </div>

                <div
                    class="relative z-10 grid gap-4 px-10 pb-10 sm:grid-cols-3"
                >
                    <div
                        v-for="stat in panelStats ?? []"
                        :key="stat.label"
                        class="rounded-3xl border border-white/14 bg-white/10 p-5 backdrop-blur-sm"
                    >
                        <p
                            class="text-2xl font-semibold tracking-[-0.03em] text-white"
                        >
                            {{ stat.value }}
                        </p>
                        <p class="mt-1 text-sm text-teal-50/74">
                            {{ stat.label }}
                        </p>
                    </div>
                </div>
            </aside>

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
                                <Link
                                    :href="home()"
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
                                            {{ page.props.name ?? 'Troskovi' }}
                                        </p>
                                        <p
                                            class="text-sm font-semibold text-foreground"
                                        >
                                            Smart money flow
                                        </p>
                                    </div>
                                </Link>
                                <span
                                    v-if="panelBadge"
                                    class="rounded-full bg-primary/10 px-3 py-1 text-[11px] font-semibold tracking-[0.2em] text-primary uppercase"
                                >
                                    {{ panelBadge }}
                                </span>
                            </div>

                            <div class="space-y-3">
                                <p
                                    v-if="eyebrow"
                                    class="text-sm font-semibold tracking-[0.32em] text-primary/80 uppercase"
                                >
                                    {{ eyebrow }}
                                </p>
                                <h1
                                    class="text-3xl font-semibold tracking-[-0.04em] text-foreground sm:text-4xl"
                                >
                                    {{ title }}
                                </h1>
                                <p
                                    class="max-w-md text-sm leading-7 text-muted-foreground sm:text-base"
                                >
                                    {{ description }}
                                </p>
                            </div>
                        </div>

                        <slot />
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>
