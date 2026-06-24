<template>
  <div class="room-view">

    <!-- ── Navbar ─────────────────────────────────────────────────── -->
    <header class="room-view__navbar">
      <!-- Lobby mode -->
      <template v-if="isWaiting">
        <div class="room-view__navbar-left">
          <div class="room-view__icon">💀</div>
          <div>
            <h1 class="room-view__room-name">{{ store.room?.name ?? 'Loading…' }}</h1>
            <p class="room-view__room-sub">Game lobby · waiting for players</p>
          </div>
        </div>

        <div class="room-view__navbar-right">
          <div class="room-view__code-pill">
            <span class="room-view__code-label">Room code</span>
            <span class="room-view__code-value">{{ store.room?.code ?? '——' }}</span>
            <button class="room-view__copy-btn" :aria-label="copied ? 'Copied!' : 'Copy room code'" @click="copyCode">
              {{ copied ? '✓' : '⎘' }}
            </button>
          </div>
          <div class="room-view__player-pill">
            <span>👥</span>
            <span class="room-view__player-count">
              {{ store.players.length }}<span class="room-view__player-max">/{{ store.room?.max_players ?? '?' }}</span>
            </span>
          </div>
        </div>
      </template>

      <!-- Game mode navbar -->
      <template v-else>
        <div class="room-view__navbar-left">
          <span class="room-view__phase-badge" :class="`room-view__phase-badge--${store.currentPhase}`">
            {{ phaseLabel }}
          </span>
          <span class="room-view__round-label">Round {{ store.round }}</span>
        </div>
        <div class="room-view__navbar-right">
          <div v-if="store.phaseEndsAt" class="room-view__timer"
            :class="{ 'room-view__timer--urgent': timerSeconds <= 10 }">
            {{ formattedTimer }}
          </div>
        </div>
      </template>
    </header>

    <!-- ── Body ───────────────────────────────────────────────────── -->
    <div class="room-view__body">

      <!-- Left: Player list -->
      <PlayerList :players="store.players" :current-phase="store.currentPhase" :my-role="store.myRole"
        :my-mafia-team="store.myMafiaTeam" :max-players="store.room?.max_players ?? 12" />

      <!-- Right: Chat + action panels -->
      <div class="room-view__main">
        <ChatBox :messages="store.messages" :phase="store.currentPhase" :disabled="false" @send="handleSendMessage" />

        <!-- Night action panel -->
        <NightActionPanel v-if="store.currentPhase === 'night' && store.session" :my-role="store.myRole ?? 'villager'"
          :alive-players="store.alivePlayers" :my-mafia-team="store.myMafiaTeam" :session-id="store.session.id"
          :round="store.round" @action-submitted="handleNightAction" />

        <!-- Day vote panel -->
        <DayVotePanel v-if="store.currentPhase === 'day_vote' && store.session" :alive-players="store.alivePlayers"
          :session-id="store.session.id" :round="store.round" :my-user-id="auth.user?.id" :vote-counts="voteCounts"
          @vote-submitted="handleVote" />

        <!-- Lobby actions -->
        <div v-if="isWaiting" class="room-view__lobby-actions">
          <button class="room-view__btn room-view__btn--outline" @click="handleLeave">
            🚪 Leave Room
          </button>
          <template v-if="store.isOwner">
            <button class="room-view__btn room-view__btn--ghost" @click="settingsOpen = true">
              ⚙️ Settings
            </button>
            <button class="room-view__btn room-view__btn--primary" :disabled="store.players.length < 4"
              @click="handleStartGame">
              ▶ Start Game
            </button>
          </template>
        </div>
      </div>
    </div>

    <!-- ── Phase Transition overlay ───────────────────────────────── -->
    <PhaseTransition :show="showTransition" :phase="transitionPhase" :role="store.myRole ?? 'villager'"
      :winner="store.winner" :night-duration="nightDuration" @done="showTransition = false" />

    <!-- ── Simple Settings modal (lobby only) ─────────────────────── -->
    <Teleport to="body">
      <div v-if="settingsOpen" class="room-view__modal-overlay" @click.self="settingsOpen = false">
        <div class="room-view__modal">
          <div class="room-view__modal-header">
            <h2>Game Rules</h2>
            <button class="room-view__modal-close" @click="settingsOpen = false">✕</button>
          </div>
          <p class="room-view__modal-desc">Configure the match before starting. Only the host can change these.</p>
          <div class="room-view__modal-body">
            <label class="room-view__setting-row">
              <span>Mafia members</span>
              <input v-model.number="settings.mafiaCount" type="range" min="1" max="4" />
              <span class="room-view__setting-val">{{ settings.mafiaCount }}</span>
            </label>
            <label class="room-view__setting-row">
              <span>Night phase</span>
              <input v-model.number="settings.nightTime" type="range" min="15" max="90" step="5" />
              <span class="room-view__setting-val">{{ settings.nightTime }}s</span>
            </label>
            <label class="room-view__setting-row">
              <span>Day phase</span>
              <input v-model.number="settings.dayTime" type="range" min="30" max="240" step="10" />
              <span class="room-view__setting-val">{{ settings.dayTime }}s</span>
            </label>
            <hr class="room-view__modal-sep" />
            <label class="room-view__toggle-row">
              <div>
                <span>Detective role</span>
                <small>Investigate one player each night</small>
              </div>
              <input v-model="settings.detective" type="checkbox" />
            </label>
            <label class="room-view__toggle-row">
              <div>
                <span>Doctor role</span>
                <small>Protect a player from elimination</small>
              </div>
              <input v-model="settings.doctor" type="checkbox" />
            </label>
            <label class="room-view__toggle-row">
              <div>
                <span>Reveal role on death</span>
                <small>Show the role of eliminated players</small>
              </div>
              <input v-model="settings.revealRole" type="checkbox" />
            </label>
          </div>
          <div class="room-view__modal-footer">
            <button class="room-view__btn room-view__btn--ghost" @click="settingsOpen = false">Cancel</button>
            <button class="room-view__btn room-view__btn--primary" @click="settingsOpen = false">Save rules</button>
          </div>
        </div>
      </div>
    </Teleport>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

import { useRoomStore } from '../store'
import { useAuthStore } from '@/modules/auth/store'
import PlayerList from '../components/PlayerList.vue'
import ChatBox from '../components/ChatBox.vue'
import NightActionPanel from '../components/NightActionPanel.vue'
import DayVotePanel from '../components/DayVotePanel.vue'
import PhaseTransition from '../components/PhaseTransition.vue'

const route = useRoute()
const router = useRouter()
const store = useRoomStore()
const auth = useAuthStore()

const roomId = route.params.id
const copied = ref(false)
const settingsOpen = ref(false)
const showTransition = ref(false)
const transitionPhase = ref('role_assignment')
const voteCounts = ref({})

const settings = ref({
  mafiaCount: 2,
  nightTime: 45,
  dayTime: 120,
  detective: true,
  doctor: true,
  revealRole: false,
})

const nightDuration = computed(() => settings.value.nightTime)

// ── Computed ──────────────────────────────────────────────────────────────────
const isWaiting = computed(() => store.currentPhase === 'waiting')

const phaseLabel = computed(() => {
  const map = {
    night: '🌙 Night Phase',
    day_vote: '☀️ Day Vote',
    day_announcement: '📢 Announcement',
    finished: '🏁 Game Over',
  }
  return map[store.currentPhase] ?? store.currentPhase
})

// ── Timer ─────────────────────────────────────────────────────────────────────
const timerSeconds = ref(0)
let timerInterval = null

const formattedTimer = computed(() => {
  const s = timerSeconds.value
  const m = Math.floor(s / 60)
  const sec = s % 60
  return `${String(m).padStart(2, '0')}:${String(sec).padStart(2, '0')}`
})

function startPhaseTimer() {
  clearInterval(timerInterval)
  if (!store.phaseEndsAt) return
  const update = () => {
    const diff = Math.max(0, Math.round((new Date(store.phaseEndsAt) - Date.now()) / 1000))
    timerSeconds.value = diff
  }
  update()
  timerInterval = setInterval(update, 1000)
}

watch(() => store.phaseEndsAt, startPhaseTimer)

// ── Methods ───────────────────────────────────────────────────────────────────
function copyCode() {
  navigator.clipboard?.writeText(store.room?.code ?? '')
  copied.value = true
  setTimeout(() => (copied.value = false), 1500)
}

async function handleSendMessage(text) {
  try {
    await store.sendMessage(roomId, text)
  } catch {
    // mesaj WebSocket üzerinden zaten gelecek
  }
}

async function handleNightAction(payload) {
  if (!store.session) return
  await store.submitNightAction(store.session.id, payload)
}

async function handleVote(payload) {
  if (!store.session) return
  await store.submitVote(store.session.id, payload)
}

async function handleStartGame() {
  await store.startGame(roomId)
}

function handleLeave() {
  router.push({ name: 'home' })
}

// ── Echo / Reverb ─────────────────────────────────────────────────────────────
let echo = null

function setupEcho() {
  window.Pusher = Pusher
  echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT,
    wssPort: import.meta.env.VITE_REVERB_PORT,
    forceTLS: false,
    enabledTransports: ['ws'],
  })

  // 1. Presence channel — lobby oyuncu listesi
  echo
    .join(`room.${roomId}`)
    .here((users) => { store.players = users })
    .joining((user) => {
      if (!store.players.find((p) => p.user_id === user.user_id)) {
        store.players.push(user)
      }
    })
    .leaving((user) => {
      store.players = store.players.filter((p) => p.user_id !== user.user_id)
    })
    .listen('.chat.message', (e) => {
      store.messages.push(e)
    })

  // 2. Session varsa game channel'ı kur
  if (store.session) {
    setupGameChannel()
  }

  // 3. Private player channel — rol ve komiser sonucu
  echo
    .private(`player.${auth.user.id}`)
    .listen('.role.assigned', (e) => {
      store.myRole = e.role
      store.myMafiaTeam = e.mafia_team ?? []
      transitionPhase.value = 'role_assignment'
      showTransition.value = true

      // Rol geldikten sonra session kanalını da kur (henüz kurulmadıysa)
      if (store.session) {
        setupGameChannel()
      }
    })
    .listen('.sheriff.result', (e) => {
      store.messages.push({
        id: crypto.randomUUID(),
        user_id: null,
        type: 'system',
        message: `🔍 Investigation result: ${e.target_nickname ?? 'target'} is ${e.result === 'mafia' ? '⚠️ MAFIA' : '✅ not mafia'}`,
        created_at: new Date().toISOString(),
      })
    })
}

function setupGameChannel() {
  if (!store.session || !echo) return

  // ✅ DÜZELTİLDİ: game.X → session.X
  echo
    .private(`session.${store.session.id}`)
    .listen('.phase.changed', (e) => {
      store.currentPhase = e.phase
      store.phaseEndsAt = e.phase_ends_at
      store.round = e.round
      store.resetRoundState()

      if (e.phase === 'night') {
        transitionPhase.value = 'night'
        showTransition.value = true
      } else if (e.phase === 'day_vote') {
        transitionPhase.value = 'day_announcement'
        showTransition.value = true
      }
    })
    .listen('.bot.message', (e) => {
      store.messages.push({ ...e, user_id: null, type: 'bot' })
    })
    .listen('.player.eliminated', (e) => {
      const p = store.players.find((pl) => pl.user_id === e.user_id)
      if (p) p.is_alive = false
    })
    .listen('.vote.updated', (e) => {
      // Backend { target_user_id, vote_count } gönderiyor
      // voteCounts objesini güncelle
      voteCounts.value = {
        ...voteCounts.value,
        [e.target_user_id]: e.vote_count,
      }
    })
    .listen('.game.ended', (e) => {
      store.winner = e.winner
      store.currentPhase = 'finished'
      transitionPhase.value = 'finished'
      showTransition.value = true
    })
}

function teardownEcho() {
  if (!echo) return
  echo.leave(`room.${roomId}`)
  echo.leave(`player.${auth.user.id}`)
  if (store.session) {
    echo.leave(`session.${store.session.id}`)
  }
  echo.disconnect()
  echo = null
}

// ── Lifecycle ─────────────────────────────────────────────────────────────────
onMounted(async () => {
  store.$reset?.()
  await store.fetchRoom(roomId)
  await store.fetchMessages(roomId)
  await store.fetchSession(roomId)
  if (store.session) {
    await store.fetchPlayers(store.session.id)
  }
  startPhaseTimer()
  setupEcho()
})

onUnmounted(() => {
  teardownEcho()
  clearInterval(timerInterval)
})
</script>

<style lang="scss" scoped>
@use '@/shared/styles/variables' as *;
@use '@/shared/styles/mixins' as *;

.room-view {
  display: flex;
  flex-direction: column;
  height: 100vh;
  background: $color-background;
  color: $color-foreground;
  overflow: hidden;

  // ── Navbar ──
  &__navbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 20px;
    border-bottom: 1px solid $color-border;
    background: rgba($color-card, 0.6);
    flex-shrink: 0;
    gap: 12px;
    flex-wrap: wrap;

    @media (max-width: 600px) {
      padding: 10px 14px;
    }
  }

  &__navbar-left,
  &__navbar-right {
    display: flex;
    align-items: center;
    gap: 10px;
  }

  &__icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: rgba($color-primary, 0.15);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    border: 1px solid rgba($color-primary, 0.3);
  }

  &__room-name {
    font-size: 16px;
    font-weight: 600;
    margin: 0;
    line-height: 1.2;
  }

  &__room-sub {
    font-size: 11px;
    color: $color-muted-foreground;
    margin: 0;
  }

  &__code-pill {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 12px;
    border: 1px solid $color-border;
    border-radius: 8px;
    background: rgba($color-secondary, 0.6);
  }

  &__code-label {
    font-size: 11px;
    color: $color-muted-foreground;
    font-weight: 500;
  }

  &__code-value {
    font-family: monospace;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 0.2em;
    color: $color-foreground;
  }

  &__copy-btn {
    background: none;
    border: none;
    cursor: pointer;
    color: $color-muted-foreground;
    font-size: 14px;
    padding: 2px 4px;
    border-radius: 4px;
    transition: color 0.15s;

    &:hover {
      color: $color-foreground;
    }
  }

  &__player-pill {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border: 1px solid $color-border;
    border-radius: 8px;
    background: rgba($color-secondary, 0.6);
    font-size: 14px;
    font-weight: 600;
  }

  &__player-count {
    font-variant-numeric: tabular-nums;
  }

  &__player-max {
    color: $color-muted-foreground;
  }

  &__phase-badge {
    padding: 4px 12px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.04em;

    &--night {
      background: rgba(99, 102, 241, 0.15);
      color: #818cf8;
    }

    &--day_vote {
      background: rgba(251, 191, 36, 0.15);
      color: #fbbf24;
    }

    &--day_announcement {
      background: rgba(251, 191, 36, 0.1);
      color: #fbbf24;
    }

    &--finished {
      background: rgba(239, 68, 68, 0.15);
      color: #f87171;
    }
  }

  &__round-label {
    font-size: 12px;
    color: $color-muted-foreground;
  }

  &__timer {
    font-family: monospace;
    font-size: 20px;
    font-weight: 700;
    color: $color-foreground;
    letter-spacing: 0.05em;
    transition: color 0.3s;

    &--urgent {
      color: #ef4444;
      animation: timerPulse 0.5s ease-in-out infinite;
    }
  }

  // ── Body ──
  &__body {
    display: flex;
    flex: 1;
    min-height: 0;
    overflow: hidden;

    @media (max-width: 768px) {
      flex-direction: column;
    }
  }

  &__main {
    flex: 1;
    display: flex;
    flex-direction: column;
    min-height: 0;
    overflow: hidden;
  }

  // ── Lobby actions ──
  &__lobby-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 8px;
    padding: 12px 16px;
    border-top: 1px solid $color-border;
    background: rgba($color-card, 0.6);
    flex-shrink: 0;
  }

  &__btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 14px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: opacity 0.15s, background 0.15s;
    border: 1px solid transparent;

    &:disabled {
      opacity: 0.4;
      cursor: not-allowed;
    }

    &--primary {
      background: $color-primary;
      color: $color-primary-foreground;
      border-color: $color-primary;

      &:not(:disabled):hover {
        opacity: 0.85;
      }
    }

    &--outline {
      background: transparent;
      border-color: $color-border;
      color: $color-foreground;

      &:hover {
        background: $color-secondary;
      }
    }

    &--ghost {
      background: transparent;
      color: $color-foreground;

      &:hover {
        background: $color-secondary;
      }
    }
  }

  // ── Settings modal ──
  &__modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
  }

  &__modal {
    background: $color-card;
    border: 1px solid $color-border;
    border-radius: 12px;
    width: 100%;
    max-width: 420px;
    overflow: hidden;
  }

  &__modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid $color-border;

    h2 {
      margin: 0;
      font-size: 16px;
    }
  }

  &__modal-close {
    background: none;
    border: none;
    cursor: pointer;
    color: $color-muted-foreground;
    font-size: 16px;
    padding: 4px;
    line-height: 1;

    &:hover {
      color: $color-foreground;
    }
  }

  &__modal-desc {
    padding: 12px 20px 0;
    font-size: 13px;
    color: $color-muted-foreground;
    margin: 0;
  }

  &__modal-body {
    padding: 16px 20px;
    display: flex;
    flex-direction: column;
    gap: 14px;
  }

  &__modal-sep {
    border: none;
    border-top: 1px solid $color-border;
    margin: 4px 0;
  }

  &__setting-row {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 13px;
    color: $color-foreground;
    cursor: default;

    span:first-child {
      flex: 1;
    }

    input[type='range'] {
      flex: 1.5;
      accent-color: $color-primary;
    }
  }

  &__setting-val {
    font-family: monospace;
    font-size: 13px;
    font-weight: 700;
    color: $color-primary;
    min-width: 36px;
    text-align: right;
  }

  &__toggle-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    font-size: 13px;
    cursor: pointer;

    div {
      display: flex;
      flex-direction: column;
      gap: 2px;

      span {
        font-weight: 500;
        color: $color-foreground;
      }

      small {
        font-size: 11px;
        color: $color-muted-foreground;
      }
    }

    input[type='checkbox'] {
      width: 16px;
      height: 16px;
      accent-color: $color-primary;
      cursor: pointer;
    }
  }

  &__modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
    padding: 12px 20px;
    border-top: 1px solid $color-border;
    background: rgba($color-muted, 0.3);
  }
}

@keyframes timerPulse {

  0%,
  100% {
    transform: scale(1);
  }

  50% {
    transform: scale(1.05);
  }
}
</style>
