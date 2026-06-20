<template>
    <button type="button" :disabled="disabled" class="room-card" :class="{
        'room-card--disabled': disabled,
        'room-card--playing': isPlaying,
    }" @click="!disabled && emit('join', room.code)">
        <!-- Code badge -->
        <span class="room-card__code" :class="{ 'room-card__code--playing': isPlaying }">
            {{ room.code }}
        </span>

        <!-- Info -->
        <span class="room-card__info">
            <span class="room-card__top-row">
                <span class="room-card__name">{{ room.name }}</span>
                <span class="room-card__badge"
                    :class="isPlaying ? 'room-card__badge--playing' : 'room-card__badge--waiting'">
                    <span class="room-card__badge-dot" :class="{ 'room-card__badge-dot--pulse': !isPlaying }" />
                    {{ isPlaying ? 'Playing' : 'Waiting' }}
                </span>
            </span>
            <span class="room-card__meta">
                <span class="room-card__host">Host {{ room.owner?.nickname }}</span>
                <span class="room-card__players">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                        <circle cx="9" cy="7" r="4" />
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                        <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                    </svg>
                    {{ room.current_players }}/{{ room.max_players }}
                </span>
            </span>
        </span>

        <!-- Arrow (only when joinable) -->
        <svg v-if="!disabled" class="room-card__arrow" xmlns="http://www.w3.org/2000/svg" width="16" height="16"
            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
            stroke-linejoin="round">
            <line x1="5" y1="12" x2="19" y2="12" />
            <polyline points="12 5 19 12 12 19" />
        </svg>
    </button>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    room: {
        type: Object,
        required: true,
    },
})

const emit = defineEmits(['join'])

const isPlaying = computed(() => props.room.status === 'playing')
const isFull = computed(() => props.room.current_players >= props.room.max_players)
const disabled = computed(() => isPlaying.value || isFull.value)
</script>

<style lang="scss" scoped>
@use '@/shared/styles/variables' as *;
@use '@/shared/styles/mixins' as *;

.room-card {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    width: 100%;
    padding: 0.75rem;
    border-radius: 0.75rem;
    border: 1px solid $color-border;
    background: rgba($color-card, 0.6);
    text-align: left;
    cursor: pointer;
    transition: border-color 0.2s, background 0.2s, box-shadow 0.2s;

    &:not(&--disabled):hover {
        border-color: rgba($color-primary, 0.5);
        background: $color-card;
        box-shadow: 0 0 0 1px rgba($color-primary, 0.12), 0 4px 20px rgba($color-primary, 0.08);

        .room-card__arrow {
            transform: translateX(2px);
            color: $color-primary;
        }
    }

    &--disabled {
        opacity: 0.55;
        cursor: not-allowed;
    }

    // ─── Code badge ─────────────────────────────────────────────
    &__code {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        width: 2.75rem;
        height: 2.75rem;
        border-radius: 0.5rem;
        font-family: $font-mono;
        font-size: 0.7rem;
        font-weight: 700;
        background: $color-secondary;
        color: $color-text;

        &--playing {
            background: rgba($color-primary, 0.15);
            color: $color-primary;
        }
    }

    // ─── Info block ─────────────────────────────────────────────
    &__info {
        flex: 1;
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 0.2rem;
    }

    &__top-row {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    &__name {
        font-size: 0.875rem;
        font-weight: 600;
        color: $color-text;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    &__meta {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 0.72rem;
        color: $color-text-muted;
    }

    &__host {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    &__players {
        display: flex;
        align-items: center;
        gap: 0.25rem;
        flex-shrink: 0;
    }

    // ─── Status badge ────────────────────────────────────────────
    &__badge {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.1rem 0.45rem;
        border-radius: 999px;
        font-size: 0.65rem;
        font-weight: 600;
        flex-shrink: 0;
        border: 1px solid transparent;

        &--playing {
            background: rgba($color-primary, 0.15);
            color: $color-primary;
            border-color: rgba($color-primary, 0.3);
        }

        &--waiting {
            background: $color-secondary;
            color: $color-text-muted;
        }
    }

    &__badge-dot {
        width: 0.4rem;
        height: 0.4rem;
        border-radius: 50%;
        background: currentColor;

        &--pulse {
            animation: badge-pulse 1.8s ease-in-out infinite;
        }
    }

    // ─── Arrow ──────────────────────────────────────────────────
    &__arrow {
        flex-shrink: 0;
        color: $color-text-muted;
        transition: transform 0.2s, color 0.2s;
    }
}

@keyframes badge-pulse {

    0%,
    100% {
        opacity: 1;
    }

    50% {
        opacity: 0.35;
    }
}
</style>