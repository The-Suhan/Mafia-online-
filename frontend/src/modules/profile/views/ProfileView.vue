<script setup>
import { computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useProfileStore } from '../store'
import { useAuthStore } from '@/modules/auth/store'
import BaseButton from '@/shared/components/BaseButton.vue'

const route = useRoute()
const router = useRouter()
const store = useProfileStore()
const auth = useAuthStore()

const userId = computed(() => route.params.id)
const isOwnProfile = computed(() =>
    auth.user && store.profile && String(auth.user.id) === String(store.profile.id),
)

// Role → colour class
const roleColour = {
    mafia: 'role--mafia',
    godfather: 'role--mafia',
    silencer: 'role--mafia',
    sheriff: 'role--sheriff',
    doctor: 'role--doctor',
    vigilante: 'role--vigilante',
    bodyguard: 'role--bodyguard',
    mayor: 'role--mayor',
    jester: 'role--jester',
    villager: 'role--villager',
    executioner: 'role--villager',
}

// Rank → CSS modifier
const rankClass = (rank) => `rank-badge--${rank?.toLowerCase() ?? 'rookie'}`

function roleClass(role) {
    return roleColour[role?.toLowerCase()] ?? 'role--villager'
}

onMounted(async () => {
    await store.fetchProfile(userId.value)
    if (store.profile) {
        store.fetchRecentGames(userId.value)
    }
})
</script>

<template>
    <main class="profile-page">
        <div class="profile-page__inner">

            <!-- ── Top bar ── -->
            <header class="profile-page__topbar">
                <span class="profile-page__brand">MAFIA</span>
                <span class="profile-page__subtitle">Player Profile</span>
            </header>

            <!-- ── Loading skeleton ── -->
            <template v-if="store.loading">
                <div class="profile-page__skeleton">
                    <div class="skeleton skeleton--avatar"></div>
                    <div class="skeleton-lines">
                        <div class="skeleton skeleton--line skeleton--lg"></div>
                        <div class="skeleton skeleton--line skeleton--md"></div>
                        <div class="skeleton skeleton--line skeleton--sm"></div>
                    </div>
                </div>
                <div class="profile-page__skeleton-stats">
                    <div v-for="n in 4" :key="n" class="skeleton skeleton--card"></div>
                </div>
            </template>

            <!-- ── Error state ── -->
            <div v-else-if="store.error" class="profile-page__error">
                {{ store.error }}
            </div>

            <!-- ── Profile content ── -->
            <template v-else-if="store.profile">

                <!-- Profile header card -->
                <section class="profile-page__header">

                    <!-- Avatar + rank badge -->
                    <div class="profile-page__avatar-wrap">
                        <div class="profile-page__avatar-frame"
                            :class="`avatar-frame--${store.profile.rank?.toLowerCase() ?? 'rookie'}`">
                            <img :src="store.avatarUrl" :alt="`${store.profile.nickname} avatar`"
                                class="profile-page__avatar-img" />
                        </div>
                        <span class="rank-badge" :class="rankClass(store.profile.rank)">
                            {{ store.profile.rank?.toUpperCase() ?? 'ROOKIE' }}
                        </span>
                    </div>

                    <!-- Identity + XP -->
                    <div class="profile-page__identity">
                        <div class="profile-page__name-row">
                            <h1 class="profile-page__nickname">{{ store.profile.nickname }}</h1>
                            <p class="profile-page__handle">@{{ store.profile.nickname?.toLowerCase() }}</p>
                        </div>

                        <!-- Edit Profile button (own profile only) -->
                        <BaseButton v-if="isOwnProfile" variant="outline" class="profile-page__edit-btn"
                            @click="router.push('/profile/edit')">
                            Edit Profile
                        </BaseButton>

                        <!-- XP progress -->
                        <div v-if="store.rankProgress && store.profile.rank?.toLowerCase() !== 'legend'"
                            class="profile-page__xp">
                            <div class="profile-page__xp-header">
                                <span class="profile-page__xp-label">
                                    PROGRESS TO {{ store.rankProgress.nextRank?.toUpperCase() }}
                                </span>
                                <span class="profile-page__xp-values">
                                    {{ (store.profile.xp ?? 0).toLocaleString() }} /
                                    {{ (store.rankProgress.nextMin ?? 0).toLocaleString() }} XP
                                </span>
                                <span class="profile-page__xp-percent">{{ store.rankProgress.progress }}%</span>
                            </div>
                            <div class="profile-page__xp-track">
                                <div class="profile-page__xp-fill"
                                    :style="{ width: store.rankProgress.progress + '%' }"></div>
                            </div>
                            <p class="profile-page__xp-footer">
                                {{ store.rankProgress.xpNeeded.toLocaleString() }} XP until
                                {{ store.rankProgress.nextRank }} rank
                            </p>
                        </div>
                        <div v-else-if="store.profile.rank?.toLowerCase() === 'legend'" class="profile-page__xp">
                            <p class="profile-page__xp-footer profile-page__xp-footer--legend">
                                Maximum rank achieved — Legend
                            </p>
                        </div>
                    </div>
                </section>

                <!-- Stats grid -->
                <section class="profile-page__stats" aria-label="Player statistics">
                    <div class="stats-grid">
                        <div class="stats-grid__card">
                            <div class="stats-grid__icon-row">
                                <!-- gamepad SVG -->
                                <svg class="stats-grid__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <rect x="2" y="6" width="20" height="12" rx="4" />
                                    <path d="M8 12h2m-1-1v2M15 12h.01M17 12h.01" />
                                </svg>
                                <span class="stats-grid__label">Total Games</span>
                            </div>
                            <span class="stats-grid__value">{{ (store.profile.total_games ?? 0).toLocaleString()
                                }}</span>
                        </div>

                        <div class="stats-grid__card stats-grid__card--wins">
                            <div class="stats-grid__icon-row">
                                <svg class="stats-grid__icon stats-grid__icon--wins" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2">
                                    <path d="M8 21h8m-4-4v4M5 3h14l-1 8a6 6 0 01-12 0L5 3z" />
                                </svg>
                                <span class="stats-grid__label">Wins</span>
                            </div>
                            <span class="stats-grid__value stats-grid__value--wins">{{ (store.profile.wins ??
                                0).toLocaleString() }}</span>
                        </div>

                        <div class="stats-grid__card stats-grid__card--losses">
                            <div class="stats-grid__icon-row">
                                <svg class="stats-grid__icon stats-grid__icon--losses" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2">
                                    <path
                                        d="M17 8C8 10 5.9 16.17 3.82 19.5a2 2 0 001.72 3h13.92a2 2 0 001.72-3C19.5 16.83 16 11 17 8z" />
                                </svg>
                                <span class="stats-grid__label">Losses</span>
                            </div>
                            <span class="stats-grid__value stats-grid__value--losses">{{ (store.profile.losses ??
                                0).toLocaleString() }}</span>
                        </div>

                        <div class="stats-grid__card stats-grid__card--winrate">
                            <div class="stats-grid__icon-row">
                                <svg class="stats-grid__icon stats-grid__icon--winrate" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2">
                                    <line x1="19" y1="5" x2="5" y2="19" />
                                    <circle cx="6.5" cy="6.5" r="2.5" />
                                    <circle cx="17.5" cy="17.5" r="2.5" />
                                </svg>
                                <span class="stats-grid__label">Win Rate</span>
                            </div>
                            <span class="stats-grid__value stats-grid__value--winrate">{{ store.winRate }}%</span>
                        </div>
                    </div>
                </section>

                <!-- Recent games table -->
                <section class="profile-page__games" aria-label="Recent match history">
                    <h2 class="profile-page__section-title">RECENT MATCHES</h2>

                    <div class="games-table">
                        <!-- Header -->
                        <div class="games-table__head">
                            <span>Match</span>
                            <span>Role</span>
                            <span>Players</span>
                            <span>Date</span>
                            <span>Result</span>
                        </div>

                        <!-- Loading games -->
                        <template v-if="store.recentGames.length === 0 && !store.loading">
                            <div class="games-table__empty">No recent matches found.</div>
                        </template>

                        <!-- Rows -->
                        <div v-for="game in store.recentGames" :key="game.id" class="games-table__row">
                            <span class="games-table__match-id">#{{ game.id }}</span>

                            <span class="games-table__role" :class="roleClass(game.role)">
                                {{ game.role }}
                            </span>

                            <span class="games-table__players">
                                {{ game.player_count ?? game.players ?? '—' }}
                            </span>

                            <span class="games-table__date">{{ game.date ?? game.played_at ?? '—' }}</span>

                            <span class="games-table__badge" :class="game.result?.toLowerCase() === 'won' || game.result?.toLowerCase() === 'win'
                                ? 'games-table__badge--win'
                                : 'games-table__badge--loss'">
                                {{ game.result?.toLowerCase() === 'won' || game.result?.toLowerCase() === 'win' ? 'WIN'
                                : 'LOSS' }}
                            </span>
                        </div>
                    </div>
                </section>

            </template>

        </div>
    </main>
</template>

<style lang="scss" scoped>
@use '@/shared/styles/variables' as *;
@use '@/shared/styles/mixins' as *;

// ── Page shell ───────────────────────────────────────────────────────────────

.profile-page {
    min-height: 100vh;
    background: $color-bg;
    color: $color-text;

    &__inner {
        max-width: 680px;
        margin: 0 auto;
        padding: 2.5rem 1.25rem 4rem;
        display: flex;
        flex-direction: column;
        gap: 2rem;
    }

    // ── Top bar
    &__topbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    &__brand {
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 0.2em;
        color: $color-destructive;
    }

    &__subtitle {
        font-size: 0.7rem;
        color: $color-text-muted;
    }

    // ── Error
    &__error {
        text-align: center;
        padding: 3rem 1rem;
        color: $color-text-muted;
        font-size: 0.95rem;
    }

    // ── Section title
    &__section-title {
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.12em;
        color: $color-text-muted;
        margin-bottom: 0.75rem;
    }
}

// ── Skeleton ─────────────────────────────────────────────────────────────────

@keyframes shimmer {
    0% {
        background-position: -400px 0;
    }

    100% {
        background-position: 400px 0;
    }
}

%skeleton-base {
    border-radius: 8px;
    background: linear-gradient(90deg,
            $color-card 25%,
            lighten($color-card, 4%) 50%,
            $color-card 75%);
    background-size: 800px 100%;
    animation: shimmer 1.4s infinite linear;
}

.skeleton {
    @extend %skeleton-base;

    &--avatar {
        width: 112px;
        height: 112px;
        border-radius: 16px;
        flex-shrink: 0;
    }

    &--line {
        height: 14px;
        border-radius: 6px;
    }

    &--lg {
        width: 220px;
    }

    &--md {
        width: 160px;
    }

    &--sm {
        width: 120px;
    }

    &--card {
        height: 80px;
        border-radius: 12px;
    }
}

.profile-page__skeleton {
    display: flex;
    gap: 1.5rem;
    align-items: flex-start;
}

.skeleton-lines {
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
    padding-top: 0.5rem;
    flex: 1;
}

.profile-page__skeleton-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0.75rem;

    @media (max-width: 540px) {
        grid-template-columns: repeat(2, 1fr);
    }
}

// ── Profile header card ───────────────────────────────────────────────────────

.profile-page__header {
    display: flex;
    gap: 2rem;
    align-items: flex-start;
    background: $color-card;
    border: 1px solid rgba($color-destructive, 0.25);
    border-radius: 16px;
    padding: 1.5rem;

    @media (max-width: 560px) {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
}

// ── Avatar ───────────────────────────────────────────────────────────────────

.profile-page__avatar-wrap {
    position: relative;
    flex-shrink: 0;
}

.profile-page__avatar-frame {
    border-radius: 16px;
    border: 2px solid $color-destructive;
    overflow: hidden;
    box-shadow: 0 0 36px -6px rgba(220, 38, 38, 0.7);
    transition: box-shadow 0.3s ease;

    &--rookie {
        border-color: #71717a;
        box-shadow: 0 0 30px -8px rgba(161, 161, 170, 0.5);
    }

    &--novice {
        border-color: #38bdf8;
        box-shadow: 0 0 30px -8px rgba(56, 189, 248, 0.6);
    }

    &--elite {
        border-color: $color-destructive;
        box-shadow: 0 0 36px -6px rgba(220, 38, 38, 0.7);
    }

    &--pro {
        border-color: #f97316;
        box-shadow: 0 0 36px -6px rgba(249, 115, 22, 0.6);
    }

    &--master {
        border-color: #a855f7;
        box-shadow: 0 0 36px -6px rgba(168, 85, 247, 0.6);
    }

    &--legend {
        border-color: #facc15;
        box-shadow: 0 0 36px -6px rgba(250, 204, 21, 0.7);
    }
}

.profile-page__avatar-img {
    width: 112px;
    height: 112px;
    display: block;
    object-fit: cover;
}

// ── Rank badge ────────────────────────────────────────────────────────────────

.rank-badge {
    position: absolute;
    bottom: -12px;
    left: 50%;
    transform: translateX(-50%);
    border-radius: 9999px;
    border: 1px solid currentColor;
    padding: 2px 10px;
    font-size: 0.6rem;
    font-weight: 800;
    letter-spacing: 0.15em;
    white-space: nowrap;
    background: $color-bg;

    &--rookie {
        color: #a1a1aa;
        border-color: #71717a;
    }

    &--novice {
        color: #7dd3fc;
        border-color: #38bdf8;
    }

    &--elite {
        color: $color-destructive;
        border-color: $color-destructive;
    }

    &--pro {
        color: #fb923c;
        border-color: #f97316;
    }

    &--master {
        color: #c084fc;
        border-color: #a855f7;
    }

    &--legend {
        color: #fde047;
        border-color: #facc15;
        background: linear-gradient(135deg, #451a03 0%, #78350f 100%);
    }
}

// ── Identity ──────────────────────────────────────────────────────────────────

.profile-page__identity {
    display: flex;
    flex-direction: column;
    gap: 0.875rem;
    flex: 1;
}

.profile-page__name-row {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
}

.profile-page__nickname {
    font-size: 1.875rem;
    font-weight: 800;
    letter-spacing: -0.02em;
    line-height: 1.1;
    margin: 0;
}

.profile-page__handle {
    font-size: 0.8rem;
    color: $color-text-muted;
    margin: 0;
}

.profile-page__edit-btn {
    align-self: flex-start;
}

// ── XP bar ────────────────────────────────────────────────────────────────────

.profile-page__xp {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}

.profile-page__xp-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.7rem;
}

.profile-page__xp-label {
    font-weight: 700;
    letter-spacing: 0.1em;
    color: $color-text-muted;
    flex: 1;
}

.profile-page__xp-values {
    font-family: $font-mono;
    color: $color-text;
}

.profile-page__xp-percent {
    font-weight: 700;
    color: $color-destructive;
    min-width: 2.5rem;
    text-align: right;
}

.profile-page__xp-track {
    height: 6px;
    border-radius: 9999px;
    background: rgba(255, 255, 255, 0.08);
    overflow: hidden;
}

.profile-page__xp-fill {
    height: 100%;
    border-radius: 9999px;
    background: $color-destructive;
    transition: width 0.8s ease;
}

.profile-page__xp-footer {
    font-size: 0.68rem;
    color: $color-text-muted;
    margin: 0;

    &--legend {
        color: #fde047;
        font-weight: 600;
    }
}

// ── Stats grid ────────────────────────────────────────────────────────────────

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0.75rem;

    @media (max-width: 540px) {
        grid-template-columns: repeat(2, 1fr);
    }

    &__card {
        background: $color-card;
        border: 1px solid rgba(255, 255, 255, 0.06);
        border-radius: 12px;
        padding: 1rem;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        transition: border-color 0.2s;

        &:hover {
            border-color: rgba($color-destructive, 0.4);
        }
    }

    &__icon-row {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        color: $color-text-muted;
    }

    &__icon {
        width: 14px;
        height: 14px;
        flex-shrink: 0;
        color: $color-text;

        &--wins {
            color: #34d399;
        }

        &--losses {
            color: $color-destructive;
        }

        &--winrate {
            color: #fbbf24;
        }
    }

    &__label {
        font-size: 0.7rem;
        font-weight: 500;
    }

    &__value {
        font-size: 1.5rem;
        font-weight: 800;
        letter-spacing: -0.02em;
        font-variant-numeric: tabular-nums;

        &--wins {
            color: #34d399;
        }

        &--losses {
            color: $color-destructive;
        }

        &--winrate {
            color: #fbbf24;
        }
    }
}

// ── Games table ───────────────────────────────────────────────────────────────

.games-table {
    background: $color-card;
    border: 1px solid rgba(255, 255, 255, 0.06);
    border-radius: 12px;
    overflow: hidden;

    &__head {
        display: grid;
        grid-template-columns: 1fr 1.2fr 0.8fr 1.1fr 0.8fr;
        padding: 0.6rem 1rem;
        font-size: 0.65rem;
        font-weight: 700;
        letter-spacing: 0.1em;
        color: $color-text-muted;
        border-bottom: 1px solid rgba(255, 255, 255, 0.06);

        @media (max-width: 480px) {
            display: none;
        }
    }

    &__row {
        display: grid;
        grid-template-columns: 1fr 1.2fr 0.8fr 1.1fr 0.8fr;
        align-items: center;
        padding: 0.7rem 1rem;
        font-size: 0.8rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        transition: background 0.15s;

        &:last-child {
            border-bottom: none;
        }

        &:hover {
            background: rgba(255, 255, 255, 0.03);
        }

        @media (max-width: 480px) {
            grid-template-columns: 1fr 1fr;
            grid-template-rows: auto auto;
            gap: 0.25rem;
        }
    }

    &__empty {
        padding: 2rem 1rem;
        text-align: center;
        color: $color-text-muted;
        font-size: 0.85rem;
    }

    &__match-id {
        font-family: $font-mono;
        color: $color-text-muted;
        font-size: 0.72rem;
    }

    &__role {
        font-weight: 700;
        font-size: 0.78rem;
    }

    &__players,
    &__date {
        color: $color-text-muted;
        font-size: 0.75rem;
    }

    &__badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 2px 10px;
        border-radius: 9999px;
        font-size: 0.65rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        width: fit-content;

        &--win {
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        &--loss {
            background: rgba(220, 38, 38, 0.12);
            color: $color-destructive;
            border: 1px solid rgba($color-destructive, 0.3);
        }
    }
}

// ── Role colours ──────────────────────────────────────────────────────────────

.role--mafia {
    color: $color-destructive;
}

.role--sheriff {
    color: #facc15;
}

.role--doctor {
    color: #34d399;
}

.role--vigilante {
    color: #fb923c;
}

.role--bodyguard {
    color: #60a5fa;
}

.role--mayor {
    color: #c084fc;
}

.role--jester {
    color: #f472b6;
}

.role--villager {
    color: #a1a1aa;
}
</style>