<template>
  <aside class="player-list">
    <div class="player-list__header">
      <h2 class="player-list__title">Players</h2>
      <span class="player-list__count">
        {{ players.length }}/{{ maxPlayers }}
      </span>
    </div>

    <div class="player-list__scroll">
      <ul class="player-list__items">
        <li
          v-for="player in players"
          :key="player.user_id"
          class="player-list__item"
          :class="{
            'player-list__item--dead': !player.is_alive,
            'player-list__item--mafia-teammate':
              isMafiaTeammate(player),
          }"
        >
          <div class="player-list__avatar-wrap">
            <img
              :src="avatarUrl(player)"
              :alt="player.nickname"
              class="player-list__avatar"
            />
            <span
              class="player-list__status"
              :class="player.is_alive ? 'player-list__status--online' : 'player-list__status--dead'"
              :aria-label="player.is_alive ? 'Alive' : 'Eliminated'"
            />
          </div>

          <div class="player-list__info">
            <span class="player-list__nickname" :class="{ 'player-list__nickname--dead': !player.is_alive }">
              {{ player.nickname }}
              <span v-if="isMe(player)" class="player-list__you">(You)</span>
            </span>
            <div class="player-list__badges">
              <span v-if="player.is_owner" class="player-list__badge player-list__badge--host" title="Room host">
                👑 Host
              </span>
              <span v-if="!player.is_alive" class="player-list__badge player-list__badge--dead">💀</span>
            </div>
          </div>
        </li>

        <li
          v-for="i in emptySlots"
          :key="`empty-${i}`"
          class="player-list__item player-list__item--empty"
        >
          <div class="player-list__avatar-wrap">
            <div class="player-list__avatar-placeholder">
              <span>{{ players.length + i }}</span>
            </div>
          </div>
          <span class="player-list__waiting">Waiting…</span>
        </li>
      </ul>
    </div>
  </aside>
</template>

<script setup>
import { computed } from 'vue'
import { useAuthStore } from '@/modules/auth/store'

const props = defineProps({
  players: { type: Array, default: () => [] },
  currentPhase: { type: String, default: 'waiting' },
  myRole: { type: String, default: null },
  myMafiaTeam: { type: Array, default: () => [] },
  maxPlayers: { type: Number, default: 12 },
})

const auth = useAuthStore()

const emptySlots = computed(() =>
  Math.max(0, props.maxPlayers - props.players.length)
)

function isMe(player) {
  return player.user_id === auth.user?.id
}

function isMafiaTeammate(player) {
  if (!['mafia', 'godfather'].includes(props.myRole)) return false
  return props.myMafiaTeam.includes(player.user_id) && !isMe(player)
}

function avatarUrl(player) {
  return `https://api.dicebear.com/7.x/bottts-neutral/svg?seed=${encodeURIComponent(player.nickname)}`
}
</script>

<style lang="scss" scoped>
@use '@/shared/styles/variables' as *;
@use '@/shared/styles/mixins' as *;

.player-list {
  display: flex;
  flex-direction: column;
  width: 260px;
  min-width: 260px;
  height: 100%;
  background: $color-card;
  border-right: 1px solid $color-border;
  overflow: hidden;

  @media (max-width: 768px) {
    width: 100%;
    min-width: 0;
    height: auto;
    max-height: 220px;
    border-right: none;
    border-bottom: 1px solid $color-border;
  }

  &__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    border-bottom: 1px solid $color-border;
    flex-shrink: 0;
  }

  &__title {
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: $color-muted-foreground;
    margin: 0;
  }

  &__count {
    font-size: 11px;
    font-weight: 500;
    color: $color-foreground;
    background: $color-secondary;
    border-radius: 999px;
    padding: 2px 8px;
    font-variant-numeric: tabular-nums;
  }

  &__scroll {
    flex: 1;
    overflow-y: auto;
    scrollbar-width: thin;
    scrollbar-color: $color-border transparent;

    &::-webkit-scrollbar { width: 4px; }
    &::-webkit-scrollbar-thumb { background: $color-border; border-radius: 4px; }
  }

  &__items {
    list-style: none;
    margin: 0;
    padding: 8px;
    display: flex;
    flex-direction: column;
    gap: 2px;
  }

  &__item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px;
    border-radius: 8px;
    transition: background 0.15s;
    border: 1px solid transparent;

    &:hover {
      background: rgba(255, 255, 255, 0.04);
    }

    &--dead {
      opacity: 0.45;
    }

    &--mafia-teammate {
      border-color: rgba(220, 38, 38, 0.5);
      background: rgba(220, 38, 38, 0.06);
    }

    &--empty {
      opacity: 0.35;
      &:hover { background: transparent; }
    }
  }

  &__avatar-wrap {
    position: relative;
    flex-shrink: 0;
  }

  &__avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: 1px solid $color-border;
    background: $color-secondary;
  }

  &__avatar-placeholder {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: 1px dashed $color-border;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    color: $color-muted-foreground;
  }

  &__status {
    position: absolute;
    bottom: -1px;
    right: -1px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    border: 2px solid $color-card;

    &--online { background: #10b981; }
    &--dead { background: #6b7280; }
  }

  &__info {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 2px;
  }

  &__nickname {
    font-size: 13px;
    font-weight: 500;
    color: $color-foreground;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;

    &--dead {
      text-decoration: line-through;
      color: $color-muted-foreground;
    }
  }

  &__you {
    font-size: 11px;
    color: $color-muted-foreground;
    font-weight: 400;
    margin-left: 4px;
  }

  &__badges {
    display: flex;
    align-items: center;
    gap: 4px;
  }

  &__badge {
    font-size: 10px;
    font-weight: 600;
    padding: 1px 6px;
    border-radius: 999px;
    line-height: 1.6;

    &--host {
      background: rgba($color-primary, 0.15);
      color: $color-primary;
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }

    &--dead {
      font-size: 12px;
    }
  }

  &__waiting {
    font-size: 13px;
    color: $color-muted-foreground;
  }
}
</style>
