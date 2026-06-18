<template>
  <div class="day-panel">
    <div class="day-panel__header">
      <h3 class="day-panel__title">Vote to eliminate</h3>
    </div>

    <div v-if="submitted" class="day-panel__submitted">
      <span>🗳️</span>
      <p>Vote cast. Waiting for others...</p>
      <button class="day-panel__change" @click="submitted = false">Change vote</button>
    </div>

    <template v-else>
      <ul class="day-panel__targets">
        <li
          v-for="player in votablePlayers"
          :key="player.user_id"
          class="day-panel__target"
          :class="{ 'day-panel__target--selected': selectedId === player.user_id }"
          @click="selectedId = player.user_id"
        >
          <img
            :src="avatarUrl(player.nickname)"
            :alt="player.nickname"
            class="day-panel__avatar"
          />
          <span class="day-panel__name">{{ player.nickname }}</span>
          <span class="day-panel__vote-count" :class="{ 'day-panel__vote-count--active': (voteCounts[player.user_id] || 0) > 0 }">
            {{ voteCounts[player.user_id] || 0 }} 🗳️
          </span>
          <span v-if="selectedId === player.user_id" class="day-panel__check">✓</span>
        </li>
      </ul>

      <div class="day-panel__footer">
        <button
          class="day-panel__cast"
          :disabled="!selectedId"
          @click="castVote"
        >
          Cast Vote
        </button>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  alivePlayers: { type: Array, default: () => [] },
  sessionId: { type: Number, required: true },
  round: { type: Number, default: 1 },
  myUserId: { type: Number, default: null },
  voteCounts: { type: Object, default: () => ({}) },
})

const emit = defineEmits(['voteSubmitted'])

const selectedId = ref(null)
const submitted = ref(false)

const votablePlayers = computed(() =>
  props.alivePlayers.filter((p) => p.user_id !== props.myUserId)
)

async function castVote() {
  if (!selectedId.value) return
  emit('voteSubmitted', { target_id: selectedId.value, round: props.round })
  submitted.value = true
}

function avatarUrl(nickname) {
  return `https://api.dicebear.com/7.x/bottts-neutral/svg?seed=${encodeURIComponent(nickname)}`
}
</script>

<style lang="scss" scoped>
@use '@/shared/styles/variables' as *;

.day-panel {
  border-top: 1px solid $color-border;
  display: flex;
  flex-direction: column;
  max-height: 260px;
  background: rgba($color-card, 0.6);

  &__header {
    padding: 12px 16px 8px;
    flex-shrink: 0;
  }

  &__title {
    font-size: 13px;
    font-weight: 600;
    color: $color-muted-foreground;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    margin: 0;
  }

  &__submitted {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 16px;
    font-size: 14px;
    color: $color-foreground;
    flex-wrap: wrap;

    p { margin: 0; flex: 1; }
  }

  &__change {
    font-size: 12px;
    color: $color-primary;
    background: none;
    border: none;
    cursor: pointer;
    text-decoration: underline;
    padding: 0;
  }

  &__targets {
    list-style: none;
    margin: 0;
    padding: 0 8px;
    flex: 1;
    overflow-y: auto;
    scrollbar-width: thin;
    scrollbar-color: $color-border transparent;
    display: flex;
    flex-direction: column;
    gap: 2px;
  }

  &__target {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 10px;
    border-radius: 8px;
    border: 1px solid transparent;
    cursor: pointer;
    transition: background 0.12s, border-color 0.12s;

    &:hover { background: rgba(255, 255, 255, 0.04); }

    &--selected {
      background: rgba(251, 191, 36, 0.1);
      border-color: rgba(251, 191, 36, 0.4);
    }
  }

  &__avatar {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: $color-secondary;
    border: 1px solid $color-border;
  }

  &__name {
    flex: 1;
    font-size: 13px;
    font-weight: 500;
    color: $color-foreground;
  }

  &__vote-count {
    font-size: 12px;
    color: $color-muted-foreground;
    font-variant-numeric: tabular-nums;

    &--active {
      color: #fbbf24;
      font-weight: 600;
    }
  }

  &__check {
    font-size: 12px;
    color: #fbbf24;
    font-weight: 700;
  }

  &__footer {
    padding: 10px 16px 14px;
    flex-shrink: 0;
  }

  &__cast {
    width: 100%;
    padding: 8px;
    border-radius: 8px;
    border: none;
    background: #fbbf24;
    color: #000;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    transition: opacity 0.15s;

    &:disabled { opacity: 0.35; cursor: not-allowed; }
    &:not(:disabled):hover { opacity: 0.85; }
  }
}
</style>
