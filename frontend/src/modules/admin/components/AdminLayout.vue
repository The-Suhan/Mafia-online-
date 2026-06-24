<template>
    <div class="admin-layout">
        <aside class="admin-layout__sidebar">
            <div class="admin-layout__logo">
                <div class="admin-layout__logo-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2z" />
                        <path d="M9 9h.01M15 9h.01" />
                        <path d="M8 13s1.5 2 4 2 4-2 4-2" />
                    </svg>
                </div>
                <div class="admin-layout__logo-text">
                    <span class="admin-layout__logo-title">MAFIA ADMIN</span>
                    <span class="admin-layout__logo-sub">Admin Console</span>
                </div>
            </div>

            <nav class="admin-layout__nav">
                <RouterLink to="/admin" class="admin-layout__nav-item"
                    :class="{ 'admin-layout__nav-item--active': $route.path === '/admin' }" exact-active-class="">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="7" height="7" />
                        <rect x="14" y="3" width="7" height="7" />
                        <rect x="3" y="14" width="7" height="7" />
                        <rect x="14" y="14" width="7" height="7" />
                    </svg>
                    <span>Dashboard</span>
                </RouterLink>

                <RouterLink to="/admin/users" class="admin-layout__nav-item"
                    active-class="admin-layout__nav-item--active">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                        <circle cx="9" cy="7" r="4" />
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" />
                    </svg>
                    <span>Users</span>
                </RouterLink>

                <RouterLink to="/admin/history-match" class="admin-layout__nav-item"
                    active-class="admin-layout__nav-item--active">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="12 8 12 12 14 14" />
                        <path d="M3.05 11a9 9 0 1 1 .5 4m-.5 5v-5h5" />
                    </svg>
                    <span>Match History</span>
                </RouterLink>
            </nav>

            <div class="admin-layout__user">
                <div class="admin-layout__user-avatar">
                    {{ authStore.user?.nickname?.slice(0, 2).toUpperCase() || 'GM' }}
                </div>
                <div class="admin-layout__user-info">
                    <span class="admin-layout__user-name">{{ authStore.user?.nickname || 'Game Master' }}</span>
                    <RouterLink to="/" class="admin-layout__back-link">← Back to Game</RouterLink>
                </div>
            </div>
        </aside>

        <div class="admin-layout__content">
            <slot />
        </div>
    </div>
</template>

<script setup>
import { useAuthStore } from '@/modules/auth/store'

const authStore = useAuthStore()
</script>

<style lang="scss" scoped>
@use '@/shared/styles/variables' as *;
@use '@/shared/styles/mixins' as *;

.admin-layout {
    display: flex;
    height: 100vh;
    overflow: hidden;
    background-color: $color-bg;
    color: $color-text;

    &__sidebar {
        width: 220px;
        flex-shrink: 0;
        display: flex;
        flex-direction: column;
        background-color: $color-card;
        border-right: 1px solid rgba(255, 255, 255, 0.08);
        overflow-y: auto;
    }

    &__logo {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 1.25rem 1rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        flex-shrink: 0;
    }

    &__logo-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 8px;
        background-color: $color-primary;
        color: #fff;
        flex-shrink: 0;
    }

    &__logo-text {
        display: flex;
        flex-direction: column;
    }

    &__logo-title {
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        color: $color-text;
        line-height: 1.2;
    }

    &__logo-sub {
        font-size: 0.65rem;
        color: $color-text-muted;
        line-height: 1.2;
    }

    &__nav {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 2px;
        padding: 0.75rem 0.5rem;
    }

    &__nav-item {
        display: flex;
        align-items: center;
        gap: 0.625rem;
        padding: 0.625rem 0.75rem;
        border-radius: 6px;
        font-size: 0.875rem;
        font-weight: 500;
        color: $color-text-muted;
        text-decoration: none;
        transition: background-color 0.15s, color 0.15s;

        svg {
            flex-shrink: 0;
        }

        &:hover {
            background-color: rgba(255, 255, 255, 0.05);
            color: $color-text;
        }

        &--active {
            background-color: rgba($color-primary-raw..., 0.12);
            color: $color-primary;
        }
    }

    &__user {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 1rem;
        border-top: 1px solid rgba(255, 255, 255, 0.08);
        flex-shrink: 0;
    }

    &__user-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background-color: rgba(255, 255, 255, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
        font-weight: 700;
        color: $color-text;
        flex-shrink: 0;
    }

    &__user-info {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    &__user-name {
        font-size: 0.8rem;
        font-weight: 600;
        color: $color-text;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    &__back-link {
        font-size: 0.7rem;
        color: $color-primary;
        text-decoration: none;
        transition: opacity 0.15s;

        &:hover {
            opacity: 0.75;
        }
    }

    &__content {
        flex: 1;
        overflow-y: auto;
        background-color: $color-bg;
        padding: 2rem;
    }
}
</style>