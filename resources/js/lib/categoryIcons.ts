import {
    Briefcase,
    Car,
    CircleDot,
    Film,
    Gift,
    GraduationCap,
    HeartPulse,
    House,
    Laptop,
    ShoppingBag,
    Sparkles,
    TrendingUp,
    UtensilsCrossed,
    Zap,
} from 'lucide-vue-next';
import type { Component } from 'vue';

const CATEGORY_ICON_COMPONENTS: Record<string, Component> = {
    briefcase: Briefcase,
    car: Car,
    'circle-dot': CircleDot,
    film: Film,
    gift: Gift,
    'graduation-cap': GraduationCap,
    'heart-pulse': HeartPulse,
    home: House,
    laptop: Laptop,
    'shopping-bag': ShoppingBag,
    sparkles: Sparkles,
    'trending-up': TrendingUp,
    utensils: UtensilsCrossed,
    zap: Zap,
};

export function getCategoryIconComponent(
    icon: string | null,
): Component | null {
    if (!icon) {
        return null;
    }

    return CATEGORY_ICON_COMPONENTS[icon] ?? null;
}

export function getCategoryIconText(icon: string | null, name: string): string {
    if (!icon) {
        return name.charAt(0);
    }

    return /^[a-z0-9-]+$/i.test(icon) ? name.charAt(0) : icon;
}
