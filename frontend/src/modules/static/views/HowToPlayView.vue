<template>
    <div class="how-to-play">
        <div class="how-to-play__backdrop" aria-hidden="true"></div>
        <div class="how-to-play__scanlines" aria-hidden="true"></div>

        <header class="how-to-play__navbar">
            <div class="how-to-play__navbar-inner">
                <RouterLink to="/" class="how-to-play__logo">MAFIA</RouterLink>

                <BaseButton v-if="authStore.token" variant="primary" class="how-to-play__navbar-action" @click="goPlay">
                    Play Now
                </BaseButton>
                <BaseButton v-else variant="outline" class="how-to-play__navbar-action" @click="goSignIn">
                    Sign In
                </BaseButton>
            </div>
        </header>

        <main class="how-to-play__main">
            <div class="how-to-play__container">
                <!-- Header -->
                <header class="how-to-play__header">
                    <div class="how-to-play__header-icon">
                        <Eye class="how-to-play__header-icon-svg" aria-hidden="true" />
                    </div>
                    <h1 class="how-to-play__title">How to Play</h1>
                    <p class="how-to-play__subtitle">
                        Trust no one. Find the mafia before they find you.
                    </p>
                </header>

                <div class="how-to-play__sections">
                    <!-- Roles -->
                    <section aria-labelledby="roles-heading" class="how-to-play__section">
                        <div class="how-to-play__section-heading">
                            <span class="how-to-play__section-bar" aria-hidden="true"></span>
                            <h2 id="roles-heading" class="how-to-play__section-title">Roles</h2>
                        </div>

                        <ul class="how-to-play__roles-grid">
                            <li v-for="role in roles" :key="role.name" class="how-to-play__role-card">
                                <div class="how-to-play__role-content">
                                    <div class="how-to-play__role-icon"
                                        :class="`how-to-play__role-icon--${role.alignment}`">
                                        <component :is="role.icon" class="how-to-play__role-icon-svg"
                                            aria-hidden="true" />
                                    </div>
                                    <div class="how-to-play__role-body">
                                        <h3 class="how-to-play__role-name">{{ role.name }}</h3>
                                        <p class="how-to-play__role-description">{{ role.description }}</p>
                                        <span class="how-to-play__role-alignment">
                                            {{ alignmentLabels[role.alignment] }}
                                        </span>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </section>

                    <!-- Game Flow -->
                    <section aria-labelledby="flow-heading" class="how-to-play__section">
                        <div class="how-to-play__section-heading">
                            <span class="how-to-play__section-bar" aria-hidden="true"></span>
                            <h2 id="flow-heading" class="how-to-play__section-title">Game Flow</h2>
                        </div>

                        <ol class="how-to-play__flow-list">
                            <li v-for="(step, index) in gameFlowSteps" :key="index" class="how-to-play__flow-step">
                                <div class="how-to-play__flow-step-marker">
                                    <span class="how-to-play__flow-step-number">{{ index + 1 }}</span>
                                    <span v-if="index < gameFlowSteps.length - 1" class="how-to-play__flow-step-line"
                                        aria-hidden="true"></span>
                                </div>
                                <div class="how-to-play__flow-step-content">
                                    <p class="how-to-play__flow-step-text">{{ step }}</p>
                                </div>
                            </li>
                        </ol>
                    </section>

                    <!-- Win Conditions -->
                    <section aria-labelledby="win-heading" class="how-to-play__section">
                        <div class="how-to-play__section-heading">
                            <span class="how-to-play__section-bar" aria-hidden="true"></span>
                            <h2 id="win-heading" class="how-to-play__section-title">Win Conditions</h2>
                        </div>

                        <div class="how-to-play__win-grid">
                            <div class="how-to-play__win-card">
                                <div class="how-to-play__win-icon">
                                    <Users class="how-to-play__win-icon-svg" aria-hidden="true" />
                                </div>
                                <h3 class="how-to-play__win-title">Village Wins</h3>
                                <p class="how-to-play__win-description">All mafia members are eliminated</p>
                            </div>

                            <div class="how-to-play__win-card how-to-play__win-card--mafia">
                                <div class="how-to-play__win-icon how-to-play__win-icon--mafia">
                                    <Skull class="how-to-play__win-icon-svg" aria-hidden="true" />
                                </div>
                                <h3 class="how-to-play__win-title">Mafia Wins</h3>
                                <p class="how-to-play__win-description">
                                    Mafia members equal or outnumber remaining villagers
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- XP & Ranks -->
                    <section aria-labelledby="ranks-heading" class="how-to-play__section">
                        <div class="how-to-play__section-heading">
                            <span class="how-to-play__section-bar" aria-hidden="true"></span>
                            <h2 id="ranks-heading" class="how-to-play__section-title">XP &amp; Ranks</h2>
                        </div>

                        <div class="how-to-play__ranks-card">
                            <!-- Progress bar -->
                            <div class="how-to-play__progress-track">
                                <div class="how-to-play__progress-fill" aria-hidden="true"></div>
                                <div class="how-to-play__progress-dots" aria-hidden="true">
                                    <span v-for="tier in rankTiers" :key="tier.name"
                                        class="how-to-play__progress-dot"></span>
                                </div>
                            </div>

                            <!-- Tier labels -->
                            <ol class="how-to-play__tiers-list">
                                <li v-for="(tier, index) in rankTiers" :key="tier.name" class="how-to-play__tier">
                                    <Trophy class="how-to-play__tier-icon" :style="{ opacity: tierOpacity(index) }"
                                        aria-hidden="true" />
                                    <span class="how-to-play__tier-name">{{ tier.name }}</span>
                                    <span class="how-to-play__tier-xp">{{ formatXp(tier.xp) }} XP</span>
                                </li>
                            </ol>
                        </div>
                    </section>
                </div>
            </div>
        </main>
    </div>
</template>

<script setup>
// NOTE: This assumes `lucide-vue-next` is available in the project (the
// Vue equivalent of the `lucide-react` icons used by the original v0 page).
// Swap these imports for your project's icon set if different.
import {
    Eye,
    Users,
    Skull,
    Trophy,
    Crosshair,
    Stethoscope,
    Search,
    User,
    Sparkles,
} from 'lucide-vue-next'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/modules/auth/store'
import BaseButton from '@/shared/components/BaseButton.vue'

const router = useRouter()
const authStore = useAuthStore()

function goPlay() {
    router.push('/')
}

function goSignIn() {
    router.push('/login')
}

// Original React page pulled this from "@/lib/how-to-play-data", which
// wasn't part of the provided source. Re-created here as static, page-local
// data since this view makes no API calls.
const alignmentLabels = {
    mafia: 'Mafia',
    village: 'Village',
    neutral: 'Neutral',
}

const roles = [
    {
        name: 'Godfather',
        alignment: 'mafia',
        icon: Crosshair,
        description:
            "Leads the mafia and decides who to eliminate each night. Appears innocent to the Detective.",
    },
    {
        name: 'Mafioso',
        alignment: 'mafia',
        icon: Skull,
        description: "Carries out the Godfather's orders and eliminates a player every night.",
    },
    {
        name: 'Doctor',
        alignment: 'village',
        icon: Stethoscope,
        description: 'Protects one player each night, saving them from a mafia attack.',
    },
    {
        name: 'Detective',
        alignment: 'village',
        icon: Search,
        description: 'Investigates one player each night to learn whether they are mafia.',
    },
    {
        name: 'Villager',
        alignment: 'village',
        icon: User,
        description: 'No special ability, but a crucial vote during the day phase.',
    },
    {
        name: 'Jester',
        alignment: 'neutral',
        icon: Sparkles,
        description: 'Wins alone by tricking the village into voting them out during the day.',
    },
]

const gameFlowSteps = [
    'Day Phase: All players discuss and debate to identify the mafia among them.',
    'Voting: Players vote to eliminate the player they suspect is mafia.',
    'Night Phase: The mafia secretly choose a target to eliminate, while special roles use their abilities.',
    'Morning: The outcome of the night is revealed to all players.',
    'Repeat: The cycle continues until a win condition is met.',
]

const rankTiers = [
    { name: 'Rookie', xp: 0 },
    { name: 'Novice', xp: 500 },
    { name: 'Skilled', xp: 1500 },
    { name: 'Veteran', xp: 3500 },
    { name: 'Elite', xp: 7500 },
    { name: 'Legend', xp: 15000 },
]

function tierOpacity(index) {
    return 0.5 + (index / (rankTiers.length - 1)) * 0.5
}

function formatXp(value) {
    return value.toLocaleString('en-US')
}
</script>

<style lang="scss" scoped>
// NOTE ON TOKENS
// ---------------------------------------------------------------------
// This file is written against `@/shared/styles/_variables.scss` and
// `@/shared/styles/_mixins.scss`, which weren't included in the prompt.
// The token names below are assumptions based on naming conventions —
// rename them to match your actual variables if they differ:
//   $color-bg, $color-bg-elevated, $color-bg-secondary, $color-bg-accent,
//   $color-border, $color-primary, $color-text, $color-text-muted,
//   $radius-md, $radius-lg, $breakpoint-sm, $breakpoint-lg, $font-mono
//
// If your variables are exposed as plain CSS custom properties (e.g.
// `--color-primary`) rather than real Sass variables, `rgba($color-primary, .15)`
// below won't compile — switch those calls to `rgba(var(--color-primary-rgb), .15)`
// or equivalent, depending on how your tokens are defined.
@use '@/shared/styles/variables' as *;
@use '@/shared/styles/mixins' as *;

.how-to-play {
    position: relative;
    min-height: 100vh;
    background: $color-bg;
    color: $color-text;
    overflow: hidden;

    &__backdrop {
        position: fixed;
        inset: 0;
        z-index: 0;
        pointer-events: none;
        background: radial-gradient(ellipse 60% 50% at 50% -10%,
                rgba($color-primary, 0.18),
                transparent 70%);
    }

    &__scanlines {
        position: fixed;
        inset: 0;
        z-index: 0;
        pointer-events: none;
        opacity: 0.04;
        background-image: repeating-linear-gradient(to bottom,
                rgba(255, 255, 255, 0.6) 0px,
                rgba(255, 255, 255, 0.6) 1px,
                transparent 1px,
                transparent 3px);
    }

    &__navbar {
        position: relative;
        z-index: 1;
        border-bottom: 1px solid $color-border;

        &-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            max-width: 860px;
            margin: 0 auto;
            padding: 1rem 1.25rem;
        }

        &-action {
            flex-shrink: 0;
        }
    }

    &__logo {
        font-weight: 800;
        font-size: 1.1rem;
        letter-spacing: 0.08em;
        color: $color-primary;
        text-decoration: none;
    }

    &__main {
        position: relative;
        z-index: 1;
    }

    &__container {
        max-width: 860px;
        margin: 0 auto;
        padding: 3rem 1.25rem 4rem;

        @media (min-width: $breakpoint-sm) {
            padding-top: 4rem;
            padding-bottom: 4rem;
        }
    }

    &__header {
        margin-bottom: 3rem;
        border-bottom: 1px solid $color-border;
        padding-bottom: 2.5rem;
    }

    &__header-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 48px;
        height: 48px;
        margin-bottom: 1rem;
        border-radius: $radius-lg;
        background: rgba($color-primary, 0.15);
        color: $color-primary;
        box-shadow: 0 0 0 1px rgba($color-primary, 0.3) inset;

        &-svg {
            width: 24px;
            height: 24px;
        }
    }

    &__title {
        font-size: 2.25rem;
        font-weight: 700;
        letter-spacing: -0.01em;
        line-height: 1.1;

        @media (min-width: $breakpoint-sm) {
            font-size: 3rem;
        }
    }

    &__subtitle {
        margin-top: 0.75rem;
        font-size: 1.125rem;
        color: $color-text-muted;
    }

    &__sections {
        display: flex;
        flex-direction: column;
        gap: 3.5rem;
    }

    &__section-heading {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1.5rem;
    }

    &__section-bar {
        width: 4px;
        height: 1.5rem;
        border-radius: 999px;
        background: $color-primary;
    }

    &__section-title {
        font-size: 1.5rem;
        font-weight: 700;
        letter-spacing: -0.01em;
    }

    // Roles
    &__roles-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1rem;
        list-style: none;
        padding: 0;
        margin: 0;

        @media (min-width: $breakpoint-sm) {
            grid-template-columns: repeat(2, 1fr);
        }

        @media (min-width: $breakpoint-lg) {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    &__role-card {
        border: 1px solid $color-border;
        background: $color-bg-elevated;
        border-radius: $radius-lg;
        padding: 1.25rem;
        transition: border-color 0.2s ease;

        &:hover {
            border-color: rgba($color-primary, 0.5);
        }
    }

    &__role-content {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
    }

    &__role-icon {
        display: flex;
        flex-shrink: 0;
        align-items: center;
        justify-content: center;
        width: 44px;
        height: 44px;
        border-radius: $radius-md;

        &-svg {
            width: 22px;
            height: 22px;
        }

        &--mafia {
            background: rgba($color-primary, 0.15);
            color: $color-primary;
            box-shadow: 0 0 0 1px rgba($color-primary, 0.3) inset;
        }

        &--village {
            background: $color-bg-secondary;
            color: $color-text;
            box-shadow: 0 0 0 1px $color-border inset;
        }

        &--neutral {
            background: $color-bg-accent;
            color: $color-text;
            box-shadow: 0 0 0 1px $color-border inset;
        }
    }

    &__role-body {
        min-width: 0;
    }

    &__role-name {
        font-weight: 600;
        line-height: 1;
        margin: 0;
    }

    &__role-description {
        margin-top: 0.5rem;
        font-size: 0.875rem;
        line-height: 1.6;
        color: $color-text-muted;
    }

    &__role-alignment {
        display: inline-block;
        margin-top: 0.75rem;
        font-size: 0.6875rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: rgba($color-text-muted, 0.7);
    }

    // Game Flow
    &__flow-list {
        list-style: none;
        margin: 0;
        padding-left: 0.5rem;
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    &__flow-step {
        display: flex;
        gap: 1rem;
    }

    &__flow-step-marker {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    &__flow-step-number {
        display: flex;
        flex-shrink: 0;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 999px;
        border: 1px solid rgba($color-primary, 0.4);
        background: rgba($color-primary, 0.1);
        font-size: 0.875rem;
        font-weight: 700;
        color: $color-primary;
    }

    &__flow-step-line {
        margin-top: 0.25rem;
        width: 1px;
        flex: 1;
        background: $color-border;
    }

    &__flow-step-content {
        flex: 1;
        border: 1px solid $color-border;
        background: $color-bg-elevated;
        border-radius: $radius-lg;
        padding: 1rem 1.25rem;
    }

    &__flow-step-text {
        margin: 0;
        font-size: 0.875rem;
        line-height: 1.6;
    }

    // Win Conditions
    &__win-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1rem;

        @media (min-width: $breakpoint-sm) {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    &__win-card {
        border: 1px solid $color-border;
        background: $color-bg-elevated;
        border-radius: $radius-lg;
        padding: 1.5rem;

        &--mafia {
            border-color: rgba($color-primary, 0.4);
            background: rgba($color-primary, 0.05);
        }
    }

    &__win-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 48px;
        height: 48px;
        border-radius: $radius-md;
        background: $color-bg-secondary;
        color: $color-text;
        box-shadow: 0 0 0 1px $color-border inset;

        &-svg {
            width: 24px;
            height: 24px;
        }

        &--mafia {
            background: rgba($color-primary, 0.15);
            color: $color-primary;
            box-shadow: 0 0 0 1px rgba($color-primary, 0.3) inset;
        }
    }

    &__win-title {
        margin: 1rem 0 0;
        font-size: 1.125rem;
        font-weight: 600;
    }

    &__win-description {
        margin: 0.25rem 0 0;
        font-size: 0.875rem;
        line-height: 1.6;
        color: $color-text-muted;
    }

    // Ranks
    &__ranks-card {
        border: 1px solid $color-border;
        background: $color-bg-elevated;
        border-radius: $radius-lg;
        padding: 1.5rem;
    }

    &__progress-track {
        position: relative;
        height: 8px;
        border-radius: 999px;
        background: $color-bg-secondary;
        margin-bottom: 2rem;
    }

    &__progress-fill {
        position: absolute;
        top: 0;
        bottom: 0;
        left: 0;
        width: 75%;
        border-radius: 999px;
        background: $color-primary;
    }

    &__progress-dots {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    &__progress-dot {
        width: 14px;
        height: 14px;
        border-radius: 999px;
        border: 2px solid $color-bg-elevated;
        background: $color-primary;
    }

    &__tiers-list {
        list-style: none;
        margin: 0;
        padding: 0;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;

        @media (min-width: $breakpoint-sm) {
            grid-template-columns: repeat(6, 1fr);
        }
    }

    &__tier {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
    }

    &__tier-icon {
        width: 16px;
        height: 16px;
        margin-bottom: 0.375rem;
        color: $color-primary;
    }

    &__tier-name {
        font-size: 0.875rem;
        font-weight: 600;
    }

    &__tier-xp {
        margin-top: 0.125rem;
        font-size: 0.75rem;
        font-family: $font-mono, monospace;
        color: $color-text-muted;
    }
}
</style>