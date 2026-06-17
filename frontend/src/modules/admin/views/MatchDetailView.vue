<!-- frontend/src/modules/admin/views/MatchDetailView.vue -->
<template>
    <AdminLayout>
        <div class="admin-match-detail">
            <p v-if="adminStore.selectedMatchError" class="admin-match-detail__empty">
                {{ adminStore.selectedMatchError }}
            </p>

            <p v-else-if="!selectedMatch" class="admin-match-detail__loading">
                Loading match…
            </p>

            <template v-else>
                <!-- Header -->
                <div class="admin-match-detail__header">
                    <a href="#" class="admin-match-detail__back-link" @click.prevent="goBack">
                        <ArrowLeft class="admin-match-detail__back-icon" />
                        Back to Match History
                    </a>

                    <div class="admin-match-detail__title-row">
                        <div class="admin-match-detail__title-block">
                            <span class="admin-match-detail__match-id">
                                Match {{ selectedMatch.id }}
                            </span>
                            <h1 class="admin-match-detail__room-name">
                                {{ selectedMatch.roomName }}
                            </h1>
                        </div>

                        <div class="admin-match-detail__winner-badge" :class="mafiaWon
                                ? 'admin-match-detail__winner-badge--mafia'
                                : 'admin-match-detail__winner-badge--villagers'
                            ">
                            <span class="admin-match-detail__winner-dot" />
                            {{ mafiaWon ? 'Mafia Won' : 'Villagers Won' }}
                        </div>
                    </div>

                    <div class="admin-match-detail__meta">
                        <span class="admin-match-detail__meta-item">
                            <Users class="admin-match-detail__meta-icon" />
                            {{ selectedMatch.totalPlayers }} Players
                        </span>
                        <span class="admin-match-detail__meta-item">
                            <Repeat class="admin-match-detail__meta-icon" />
                            {{ selectedMatch.totalRounds }} Rounds
                        </span>
                        <span class="admin-match-detail__meta-item">
                            <Clock class="admin-match-detail__meta-icon" />
                            Duration: {{ selectedMatch.duration }}
                        </span>
                        <span class="admin-match-detail__meta-item">
                            <Calendar class="admin-match-detail__meta-icon" />
                            Started: {{ formatDate(selectedMatch.startedAt) }}
                        </span>
                        <span class="admin-match-detail__meta-item">
                            <Calendar class="admin-match-detail__meta-icon" />
                            Ended: {{ formatDate(selectedMatch.endedAt) }}
                        </span>
                    </div>
                </div>

                <!-- Players table -->
                <section class="admin-match-detail__players">
                    <h2 class="admin-match-detail__section-title">Players</h2>

                    <div class="admin-match-detail__table-wrap">
                        <table class="admin-match-detail__table">
                            <thead>
                                <tr>
                                    <th>Player</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th class="admin-match-detail__table-right">XP Earned</th>
                                    <th class="admin-match-detail__table-right">Result</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="player in matchPlayers" :key="player.id">
                                    <td>
                                        <div class="admin-match-detail__player-cell">
                                            <div class="admin-match-detail__avatar">
                                                {{ initials(player.nickname) }}
                                            </div>
                                            <span class="admin-match-detail__nickname">
                                                {{ player.nickname }}
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="admin-match-detail__role" :class="roleClass(player.role)">
                                            <span class="admin-match-detail__role-dot"
                                                :class="roleDotClass(player.role)" />
                                            {{ formatRole(player.role) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="admin-match-detail__status" :class="player.status === 'Alive'
                                                ? 'admin-match-detail__status--alive'
                                                : 'admin-match-detail__status--eliminated'
                                            ">
                                            <span class="admin-match-detail__status-dot" />
                                            {{ player.status }}
                                        </span>
                                    </td>
                                    <td class="admin-match-detail__table-right admin-match-detail__xp">
                                        +{{ player.xp }}
                                    </td>
                                    <td class="admin-match-detail__table-right">
                                        <span class="admin-match-detail__result" :class="player.result === 'WIN'
                                                ? 'admin-match-detail__result--win'
                                                : 'admin-match-detail__result--loss'
                                            ">
                                            {{ player.result }}
                                        </span>
                                    </td>
                                </tr>

                                <tr v-if="!matchPlayers.length">
                                    <td colspan="5" class="admin-match-detail__empty-row">
                                        No players recorded for this match.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- Game timeline -->
                <section class="admin-match-detail__timeline">
                    <h2 class="admin-match-detail__section-title">Game Timeline</h2>

                    <p v-if="!timelineRounds.length" class="admin-match-detail__empty">
                        No events recorded for this match yet.
                    </p>

                    <div v-else class="admin-match-detail__rounds">
                        <div v-for="round in timelineRounds" :key="round.round" class="admin-match-detail__round">
                            <div class="admin-match-detail__round-title">
                                Round {{ round.round }}
                            </div>

                            <div class="admin-match-detail__phases">
                                <div v-for="phase in round.phases" :key="phase.type" class="admin-match-detail__phase">
                                    <span class="admin-match-detail__phase-icon" :class="phase.type === 'night'
                                            ? 'admin-match-detail__phase-icon--night'
                                            : 'admin-match-detail__phase-icon--day'
                                        ">
                                        {{ phase.type === 'night' ? '🌙' : '☀️' }}
                                    </span>

                                    <span class="admin-match-detail__phase-label">
                                        {{ phase.label }}
                                    </span>

                                    <ul class="admin-match-detail__events">
                                        <li v-for="event in phase.events" :key="event.id"
                                            class="admin-match-detail__event" :class="eventClass(event)">
                                            <span class="admin-match-detail__event-icon">
                                                {{ eventIcon(event) }}
                                            </span>
                                            {{ event.message }}
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </template>
        </div>
    </AdminLayout>
</template>

<script setup>
import { computed, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ArrowLeft, Users, Repeat, Clock, Calendar } from 'lucide-vue-next'
import { useAdminStore } from '@/modules/admin/store'
import AdminLayout from '@/modules/admin/components/AdminLayout.vue'

const route = useRoute()
const router = useRouter()
const adminStore = useAdminStore()

const matchId = computed(() => route.params.id)

const selectedMatch = computed(() => adminStore.selectedMatch)
const matchPlayers = computed(() => adminStore.matchPlayers || [])
const matchLogs = computed(() => adminStore.matchLogs || [])

const mafiaWon = computed(() => selectedMatch.value?.winner === 'MAFIA')

onMounted(() => {
    adminStore.fetchMatch(matchId.value)
    adminStore.fetchMatchLogs(matchId.value)
})

onUnmounted(() => {
    adminStore.clearSelectedMatch()
})

function goBack() {
    router.back()
}

function initials(nickname = '') {
    return nickname
        .split(' ')
        .map((n) => n[0])
        .slice(0, 2)
        .join('')
        .toUpperCase()
}

function formatRole(role) {
    if (!role) return ''
    return role.charAt(0).toUpperCase() + role.slice(1)
}

function formatDate(value) {
    if (!value) return '—'
    return new Date(value).toLocaleString('en-US', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    })
}

// --- Role styling (mirrors lib/role-styles.ts from the v0 source) ---
const ROLE_STYLES = {
    mafia: {
        text: 'admin-match-detail__role--mafia',
        dot: 'admin-match-detail__role-dot--mafia',
    },
    sheriff: {
        text: 'admin-match-detail__role--sheriff',
        dot: 'admin-match-detail__role-dot--sheriff',
    },
    doctor: {
        text: 'admin-match-detail__role--doctor',
        dot: 'admin-match-detail__role-dot--doctor',
    },
    villager: {
        text: 'admin-match-detail__role--villager',
        dot: 'admin-match-detail__role-dot--villager',
    },
}

function roleClass(role) {
    return ROLE_STYLES[role]?.text ?? ROLE_STYLES.villager.text
}
function roleDotClass(role) {
    return ROLE_STYLES[role]?.dot ?? ROLE_STYLES.villager.dot
}

// --- Timeline event styling/icons ---
// Adjust eventType keys to match whatever your `game_logs` table actually emits.
const EVENT_ICONS = {
    kill: '💀',
    save: '🛡️',
    investigate: '🔍',
    vote_elimination: '⚖️',
    vote_no_elimination: '🤝',
    role_reveal: '🎭',
}

const EVENT_HIGHLIGHTS = {
    kill: 'danger',
    vote_elimination: 'danger',
    save: 'success',
    vote_no_elimination: 'success',
    investigate: 'warning',
    role_reveal: 'warning',
}

function eventIcon(event) {
    return EVENT_ICONS[event.eventType] ?? '•'
}

function eventClass(event) {
    const highlight = event.highlight ?? EVENT_HIGHLIGHTS[event.eventType]
    switch (highlight) {
        case 'danger':
            return 'admin-match-detail__event--danger'
        case 'success':
            return 'admin-match-detail__event--success'
        case 'warning':
            return 'admin-match-detail__event--warning'
        default:
            return ''
    }
}

function phaseLabel(phase) {
    return phase === 'night' ? 'Night' : 'Day Vote'
}

// Groups the flat `matchLogs` list (as returned by GET /sessions/{id}/logs)
// into Round -> Phase -> Events, the same shape the original TSX consumed.
const timelineRounds = computed(() => {
    const roundsMap = new Map()

    for (const log of matchLogs.value) {
        if (!roundsMap.has(log.round)) {
            roundsMap.set(log.round, { round: log.round, phasesMap: new Map() })
        }
        const roundEntry = roundsMap.get(log.round)

        if (!roundEntry.phasesMap.has(log.phase)) {
            roundEntry.phasesMap.set(log.phase, {
                type: log.phase,
                label: phaseLabel(log.phase),
                events: [],
            })
        }
        roundEntry.phasesMap.get(log.phase).events.push(log)
    }

    return Array.from(roundsMap.values())
        .sort((a, b) => a.round - b.round)
        .map((entry) => ({
            round: entry.round,
            phases: Array.from(entry.phasesMap.values()),
        }))
})
</script>

<style lang="scss" scoped>
// Adjust these import paths/names to match your real partials if they differ.
@use '@/shared/styles/variables' as *;
@use '@/shared/styles/mixins' as *;

.admin-match-detail {
    display: flex;
    flex-direction: column;
    gap: 2rem;
    max-width: 56rem;
    margin: 0 auto;
    padding: 2rem 1rem;

    @include respond-up(sm) {
        padding: 2rem;
    }

    &__loading,
    &__empty {
        color: $color-text-muted;
        font-size: 0.875rem;
    }

    // ---------- Header ----------
    &__header {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    &__back-link {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        width: fit-content;
        font-size: 0.875rem;
        color: $color-text-muted;
        text-decoration: none;
        transition: color 0.15s ease;

        &:hover {
            color: $color-text;
        }
    }

    &__back-icon {
        width: 1rem;
        height: 1rem;
    }

    &__title-row {
        display: flex;
        flex-direction: column;
        gap: 1rem;

        @include respond-up(sm) {
            flex-direction: row;
            align-items: flex-start;
            justify-content: space-between;
        }
    }

    &__title-block {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    &__match-id {
        font-family: $font-mono, monospace;
        font-size: 0.875rem;
        color: $color-text-muted;
    }

    &__room-name {
        font-size: 1.5rem;
        font-weight: 600;
        letter-spacing: -0.01em;
        color: $color-text;
    }

    &__winner-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        width: fit-content;
        border-radius: $radius-md;
        border: 1px solid transparent;
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;

        &--mafia {
            border-color: rgba($color-danger, 0.3);
            background: rgba($color-danger, 0.1);
            color: $color-danger;
        }

        &--villagers {
            border-color: rgba($color-success, 0.3);
            background: rgba($color-success, 0.1);
            color: $color-success;
        }
    }

    &__winner-dot {
        width: 0.5rem;
        height: 0.5rem;
        border-radius: 50%;
        background: currentColor;
    }

    &__meta {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem 1.5rem;
        border-top: 1px solid $color-border;
        border-bottom: 1px solid $color-border;
        padding: 0.75rem 0;
        font-size: 0.875rem;
        color: $color-text-muted;
    }

    &__meta-item {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
    }

    &__meta-icon {
        width: 1rem;
        height: 1rem;
        opacity: 0.7;
    }

    // ---------- Shared section title ----------
    &__section-title {
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: $color-text-muted;
        margin-bottom: 0.75rem;
    }

    // ---------- Players table ----------
    &__players {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    &__table-wrap {
        overflow: hidden;
        overflow-x: auto;
        border-radius: $radius-lg;
        border: 1px solid $color-border;
        background: $color-card;
    }

    &__table {
        width: 100%;
        font-size: 0.875rem;
        border-collapse: collapse;

        thead tr {
            border-bottom: 1px solid $color-border;
            text-align: left;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: $color-text-muted;
        }

        th,
        td {
            padding: 0.75rem 1rem;
        }

        tbody tr {
            border-bottom: 1px solid rgba($color-border, 0.6);
            transition: background-color 0.15s ease;

            &:last-child {
                border-bottom: none;
            }

            &:hover {
                background: rgba($color-secondary, 0.4);
            }
        }
    }

    &__table-right {
        text-align: right;
    }

    &__empty-row {
        text-align: center;
        color: $color-text-muted;
        padding: 1.5rem 1rem;
    }

    &__player-cell {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    &__avatar {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        width: 2rem;
        height: 2rem;
        border-radius: 50%;
        background: $color-secondary;
        font-size: 0.75rem;
        font-weight: 600;
        color: $color-secondary-foreground;
    }

    &__nickname {
        font-weight: 500;
        color: $color-text;
    }

    &__role {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        font-weight: 500;

        &--mafia {
            color: $color-danger;
        }

        &--sheriff {
            color: $color-warning;
        }

        &--doctor {
            color: $color-success;
        }

        &--villager {
            color: $color-text-muted;
        }
    }

    &__role-dot {
        width: 0.375rem;
        height: 0.375rem;
        border-radius: 50%;

        &--mafia {
            background: $color-danger;
        }

        &--sheriff {
            background: $color-warning;
        }

        &--doctor {
            background: $color-success;
        }

        &--villager {
            background: $color-text-muted;
        }
    }

    &__status {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        border-radius: 999px;
        padding: 0.125rem 0.5rem;
        font-size: 0.75rem;
        font-weight: 500;

        &--alive {
            background: rgba($color-success, 0.1);
            color: $color-success;
        }

        &--eliminated {
            background: rgba($color-danger, 0.1);
            color: $color-danger;
        }
    }

    &__status-dot {
        width: 0.375rem;
        height: 0.375rem;
        border-radius: 50%;
        background: currentColor;
    }

    &__xp {
        font-family: $font-mono, monospace;
        color: $color-text;
    }

    &__result {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 0.125rem 0.625rem;
        font-size: 0.75rem;
        font-weight: 700;

        &--win {
            background: rgba($color-success, 0.15);
            color: $color-success;
        }

        &--loss {
            background: rgba($color-danger, 0.15);
            color: $color-danger;
        }
    }

    // ---------- Timeline ----------
    &__timeline {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    &__rounds {
        display: flex;
        flex-direction: column;
        gap: 2rem;
    }

    &__round {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    &__round-title {
        border-left: 2px solid $color-primary;
        padding-left: 0.75rem;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: $color-primary;
    }

    &__phases {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
        margin-left: 0.25rem;
        border-left: 1px solid $color-border;
        padding-left: 1.25rem;
    }

    &__phase {
        position: relative;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    &__phase-icon {
        position: absolute;
        left: -1.625rem;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 1rem;
        height: 1rem;
        border-radius: 50%;
        font-size: 0.625rem;
        line-height: 1;
        box-shadow: 0 0 0 4px $color-bg;

        &--night {
            background: $color-secondary;
        }

        &--day {
            background: rgba($color-warning, 0.2);
        }
    }

    &__phase-label {
        font-size: 0.875rem;
        font-weight: 600;
        color: $color-text;
    }

    &__events {
        display: flex;
        flex-direction: column;
        gap: 0.375rem;
        list-style: none;
        padding: 0;
        margin: 0;
    }

    &__event {
        display: flex;
        align-items: flex-start;
        gap: 0.5rem;
        font-size: 0.875rem;
        color: rgba($color-text, 0.8);

        &--danger {
            color: $color-danger;
            font-weight: 500;
        }

        &--success {
            color: $color-success;
            font-weight: 500;
        }

        &--warning {
            color: $color-warning;
            font-weight: 500;
        }
    }

    &__event-icon {
        line-height: 1.4;
    }
}
</style>