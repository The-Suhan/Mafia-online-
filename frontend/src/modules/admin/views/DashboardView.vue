<template>
    <AdminLayout>
        <div class="admin-dashboard">
            <header class="admin-dashboard__header">
                <div>
                    <h1 class="admin-dashboard__title">Dashboard</h1>
                    <p class="admin-dashboard__subtitle">Overview of your Mafia game servers</p>
                </div>
                <div class="admin-dashboard__actions">
                    <div class="admin-dashboard__search-wrap">
                        <svg class="admin-dashboard__search-icon" xmlns="http://www.w3.org/2000/svg" width="16"
                            height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8" />
                            <line x1="21" y1="21" x2="16.65" y2="16.65" />
                        </svg>
                        <input type="search" class="admin-dashboard__search" placeholder="Search players, rooms..."
                            aria-label="Search" />
                    </div>
                    <button class="admin-dashboard__bell" aria-label="Notifications">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" />
                            <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                        </svg>
                        <span class="admin-dashboard__bell-dot"></span>
                    </button>
                </div>
            </header>

            <!-- Stats Grid -->
            <div v-if="adminStore.loading" class="admin-dashboard__loading">
                <span>Loading stats…</span>
            </div>
            <div v-else-if="adminStore.error" class="admin-dashboard__error">
                {{ adminStore.error }}
            </div>
            <div v-else class="admin-dashboard__stats">
                <div v-for="stat in statsCards" :key="stat.label" class="admin-dashboard__stat-card">
                    <div class="admin-dashboard__stat-top">
                        <span class="admin-dashboard__stat-label">{{ stat.label }}</span>
                        <div class="admin-dashboard__stat-icon">
                            <component :is="stat.iconComponent" />
                        </div>
                    </div>
                    <div class="admin-dashboard__stat-value">{{ stat.value }}</div>
                    <div class="admin-dashboard__stat-delta"
                        :class="stat.trend === 'up' ? 'admin-dashboard__stat-delta--up' : 'admin-dashboard__stat-delta--down'">
                        <svg v-if="stat.trend === 'up'" xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="23 6 13.5 15.5 8.5 10.5 1 18" />
                            <polyline points="17 6 23 6 23 12" />
                        </svg>
                        <svg v-else xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <polyline points="23 18 13.5 8.5 8.5 13.5 1 6" />
                            <polyline points="17 18 23 18 23 12" />
                        </svg>
                        <span>{{ stat.delta }}</span>
                    </div>
                </div>
            </div>

            <!-- Activity Feed -->
            <section class="admin-dashboard__feed">
                <div class="admin-dashboard__feed-header">
                    <div>
                        <h2 class="admin-dashboard__feed-title">Recent Activity</h2>
                        <p class="admin-dashboard__feed-sub">Latest events across all rooms</p>
                    </div>
                    <span class="admin-dashboard__live">
                        <span class="admin-dashboard__live-dot">
                            <span class="admin-dashboard__live-ping"></span>
                        </span>
                        Live
                    </span>
                </div>

                <div v-if="adminStore.loading" class="admin-dashboard__loading">Loading activity…</div>
                <ol v-else class="admin-dashboard__feed-list">
                    <li v-for="event in adminStore.activity" :key="event.id" class="admin-dashboard__feed-item">
                        <div class="admin-dashboard__feed-icon" :class="`admin-dashboard__feed-icon--${event.type}`">
                            <component :is="eventIconComponent(event.type)" />
                        </div>
                        <div class="admin-dashboard__feed-body">
                            <p class="admin-dashboard__feed-msg">{{ eventTitle(event) }}</p>
                            <p class="admin-dashboard__feed-detail">{{ eventDetail(event) }}</p>
                        </div>
                        <span class="admin-dashboard__feed-time">{{ formatTime(event.timestamp) }}</span>
                    </li>
                </ol>
            </section>
        </div>
    </AdminLayout>
</template>

<script setup>
import { computed, onMounted, h } from 'vue'
import { useAdminStore } from '../store'
import AdminLayout from '../components/AdminLayout.vue'

const adminStore = useAdminStore()

onMounted(async () => {
    await Promise.all([adminStore.fetchStats(), adminStore.fetchActivity()])
})

// Inline SVG icon components (avoids lucide dependency in Vue)
const IconUsers = {
    render: () => h('svg', { xmlns: 'http://www.w3.org/2000/svg', width: 20, height: 20, viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', 'stroke-width': 2, 'stroke-linecap': 'round', 'stroke-linejoin': 'round' }, [
        h('path', { d: 'M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2' }),
        h('circle', { cx: 9, cy: 7, r: 4 }),
        h('path', { d: 'M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75' }),
    ])
}

const IconDoor = {
    render: () => h('svg', { xmlns: 'http://www.w3.org/2000/svg', width: 20, height: 20, viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', 'stroke-width': 2, 'stroke-linecap': 'round', 'stroke-linejoin': 'round' }, [
        h('path', { d: 'M3 21h18' }),
        h('path', { d: 'M19 21V5a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v16' }),
        h('path', { d: 'M14 12h-4' }),
    ])
}

const IconGamepad = {
    render: () => h('svg', { xmlns: 'http://www.w3.org/2000/svg', width: 20, height: 20, viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', 'stroke-width': 2, 'stroke-linecap': 'round', 'stroke-linejoin': 'round' }, [
        h('line', { x1: 6, y1: 12, x2: 10, y2: 12 }),
        h('line', { x1: 8, y1: 10, x2: 8, y2: 14 }),
        h('line', { x1: 15, y1: 13, x2: 15.01, y2: 13 }),
        h('line', { x1: 18, y1: 11, x2: 18.01, y2: 11 }),
        h('rect', { x: 2, y: 6, width: 20, height: 12, rx: 2 }),
    ])
}

const IconActivity = {
    render: () => h('svg', { xmlns: 'http://www.w3.org/2000/svg', width: 20, height: 20, viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', 'stroke-width': 2, 'stroke-linecap': 'round', 'stroke-linejoin': 'round' }, [
        h('polyline', { points: '22 12 18 12 15 21 9 3 6 12 2 12' }),
    ])
}

// Activity event icons
const IconUserPlus = {
    render: () => h('svg', { xmlns: 'http://www.w3.org/2000/svg', width: 16, height: 16, viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', 'stroke-width': 2, 'stroke-linecap': 'round', 'stroke-linejoin': 'round' }, [
        h('path', { d: 'M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2' }),
        h('circle', { cx: 8.5, cy: 7, r: 4 }),
        h('line', { x1: 20, y1: 8, x2: 20, y2: 14 }),
        h('line', { x1: 23, y1: 11, x2: 17, y2: 11 }),
    ])
}

const IconPlay = {
    render: () => h('svg', { xmlns: 'http://www.w3.org/2000/svg', width: 16, height: 16, viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', 'stroke-width': 2, 'stroke-linecap': 'round', 'stroke-linejoin': 'round' }, [
        h('polygon', { points: '5 3 19 12 5 21 5 3' }),
    ])
}

const IconFlag = {
    render: () => h('svg', { xmlns: 'http://www.w3.org/2000/svg', width: 16, height: 16, viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', 'stroke-width': 2, 'stroke-linecap': 'round', 'stroke-linejoin': 'round' }, [
        h('path', { d: 'M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z' }),
        h('line', { x1: 4, y1: 22, x2: 4, y2: 15 }),
    ])
}

const IconCirclePlus = {
    render: () => h('svg', { xmlns: 'http://www.w3.org/2000/svg', width: 16, height: 16, viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', 'stroke-width': 2, 'stroke-linecap': 'round', 'stroke-linejoin': 'round' }, [
        h('circle', { cx: 12, cy: 12, r: 10 }),
        h('line', { x1: 12, y1: 8, x2: 12, y2: 16 }),
        h('line', { x1: 8, y1: 12, x2: 16, y2: 12 }),
    ])
}

const eventIconMap = {
    user_registered: IconUserPlus,
    game_started: IconPlay,
    game_ended: IconFlag,
    room_created: IconCirclePlus,
}

function eventIconComponent(type) {
    return eventIconMap[type] || IconActivity
}

function eventTitle(event) {
    switch (event.type) {
        case 'user_registered': return `New user joined: ${event.nickname}`
        case 'game_started': return `Game started in room: ${event.room_name}`
        case 'game_ended': return `Game ended — ${event.winner} won in ${event.room_name}`
        case 'room_created': return `Room created: ${event.room_name} by ${event.nickname}`
        default: return event.message || 'Unknown event'
    }
}

function eventDetail(event) {
    return event.detail || ''
}

function formatTime(ts) {
    if (!ts) return ''
    const d = new Date(ts)
    const now = new Date()
    const diff = Math.floor((now - d) / 1000)
    if (diff < 60) return `${diff}s ago`
    if (diff < 3600) return `${Math.floor(diff / 60)}m ago`
    if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`
    return d.toLocaleDateString()
}

const statsCards = computed(() => {
    const s = adminStore.stats
    return [
        { label: 'Total Users', value: s?.total_users ?? '—', delta: s?.users_delta ?? '', trend: s?.users_trend ?? 'up', iconComponent: IconUsers },
        { label: 'Active Rooms', value: s?.active_rooms ?? '—', delta: s?.rooms_delta ?? '', trend: s?.rooms_trend ?? 'up', iconComponent: IconDoor },
        { label: 'Games Played Today', value: s?.games_today ?? '—', delta: s?.games_delta ?? '', trend: s?.games_trend ?? 'up', iconComponent: IconGamepad },
        { label: 'Online Players Now', value: s?.online_players ?? '—', delta: s?.online_delta ?? '', trend: s?.online_trend ?? 'up', iconComponent: IconActivity },
    ]
})
</script>

<style lang="scss" scoped>
@use '@/shared/styles/variables' as *;
@use '@/shared/styles/mixins' as *;

.admin-dashboard {
    display: flex;
    flex-direction: column;
    gap: 1.75rem;
    max-width: 1200px;

    &__header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
    }

    &__title {
        font-size: 1.25rem;
        font-weight: 600;
        letter-spacing: -0.01em;
        color: $color-text;
        margin: 0;
    }

    &__subtitle {
        font-size: 0.8rem;
        color: $color-text-muted;
        margin: 0.15rem 0 0;
    }

    &__actions {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    &__search-wrap {
        position: relative;
    }

    &__search-icon {
        position: absolute;
        left: 0.625rem;
        top: 50%;
        transform: translateY(-50%);
        color: $color-text-muted;
        pointer-events: none;
    }

    &__search {
        height: 36px;
        width: 220px;
        border-radius: 6px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        background-color: rgba(255, 255, 255, 0.04);
        padding: 0 0.75rem 0 2.25rem;
        font-size: 0.875rem;
        color: $color-text;
        outline: none;

        &::placeholder {
            color: $color-text-muted;
        }

        &:focus {
            border-color: $color-primary;
            box-shadow: 0 0 0 2px rgba($color-primary-raw..., 0.2);
        }
    }

    &__bell {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 6px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        background: transparent;
        color: $color-text-muted;
        cursor: pointer;
        transition: background-color 0.15s, color 0.15s;

        &:hover {
            background-color: rgba(255, 255, 255, 0.06);
            color: $color-text;
        }
    }

    &__bell-dot {
        position: absolute;
        top: 8px;
        right: 8px;
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background-color: $color-primary;
    }

    // Stats grid
    &__stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1rem;
    }

    &__stat-card {
        background-color: $color-card;
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 12px;
        padding: 1.25rem;
        transition: border-color 0.2s, box-shadow 0.2s;

        &:hover {
            box-shadow: 0 0 0 1px rgba(192, 57, 43, 0.25), 0 4px 20px rgba(192, 57, 43, 0.1);
        }
    }

    &__stat-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
    }

    &__stat-label {
        font-size: 0.8rem;
        font-weight: 500;
        color: $color-text-muted;
    }

    &__stat-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 8px;
        background-color: rgba(192, 57, 43, 0.1);
        color: $color-primary;
    }

    &__stat-value {
        margin-top: 0.75rem;
        font-size: 1.875rem;
        font-weight: 600;
        letter-spacing: -0.02em;
        color: $color-text;
    }

    &__stat-delta {
        margin-top: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.375rem;
        font-size: 0.75rem;
        font-weight: 500;

        &--up {
            color: #34d399;
        }

        &--down {
            color: $color-primary;
        }
    }

    // Activity feed
    &__feed {
        background-color: $color-card;
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 12px;
        overflow: hidden;
    }

    &__feed-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }

    &__feed-title {
        font-size: 1rem;
        font-weight: 600;
        color: $color-text;
        margin: 0;
    }

    &__feed-sub {
        font-size: 0.8rem;
        color: $color-text-muted;
        margin: 0.15rem 0 0;
    }

    &__live {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.75rem;
        font-weight: 500;
        color: $color-text-muted;
    }

    &__live-dot {
        position: relative;
        display: flex;
        width: 8px;
        height: 8px;
    }

    &__live-ping {
        position: absolute;
        inset: 0;
        border-radius: 50%;
        background-color: $color-primary;
        opacity: 0.75;
        animation: ping 1.2s cubic-bezier(0, 0, 0.2, 1) infinite;

        &::after {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 50%;
            background-color: $color-primary;
        }
    }

    &__feed-list {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    &__feed-item {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        transition: background-color 0.15s;

        &:last-child {
            border-bottom: none;
        }

        &:hover {
            background-color: rgba(255, 255, 255, 0.03);
        }
    }

    &__feed-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        flex-shrink: 0;

        &--user_registered {
            background-color: rgba(16, 185, 129, 0.1);
            color: #34d399;
        }

        &--game_started {
            background-color: rgba(192, 57, 43, 0.1);
            color: $color-primary;
        }

        &--game_ended {
            background-color: rgba(245, 158, 11, 0.1);
            color: #fbbf24;
        }

        &--room_created {
            background-color: rgba(14, 165, 233, 0.1);
            color: #38bdf8;
        }
    }

    &__feed-body {
        flex: 1;
        min-width: 0;
    }

    &__feed-msg {
        font-size: 0.875rem;
        font-weight: 500;
        color: $color-text;
        margin: 0;
    }

    &__feed-detail {
        font-size: 0.8rem;
        color: $color-text-muted;
        margin: 0.15rem 0 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    &__feed-time {
        flex-shrink: 0;
        font-size: 0.75rem;
        color: $color-text-muted;
        white-space: nowrap;
    }

    &__loading,
    &__error {
        padding: 2rem;
        text-align: center;
        color: $color-text-muted;
        font-size: 0.875rem;
    }

    &__error {
        color: $color-primary;
    }
}

@keyframes ping {

    75%,
    100% {
        transform: scale(2);
        opacity: 0;
    }
}
</style>