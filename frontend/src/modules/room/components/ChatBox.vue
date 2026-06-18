<template>
  <section class="chat-box">
    <div class="chat-box__header">
      <h2 class="chat-box__title">
        {{ phase === 'waiting' ? 'Lobby Chat' : 'Village Chat' }}
      </h2>
    </div>

    <div class="chat-box__messages" ref="scrollRef">
      <div class="chat-box__messages-inner">
        <template v-for="msg in messages" :key="msg.id ?? msg.created_at">
          <!-- System message -->
          <div v-if="msg.type === 'system'" class="chat-box__system">
            <span class="chat-box__system-text">{{ msg.message }} · {{ formatTime(msg.created_at) }}</span>
          </div>

          <!-- Bot message -->
          <div v-else-if="msg.user_id === null || msg.type === 'bot'" class="chat-box__bot">
            <span class="chat-box__bot-icon">🤖</span>
            <p class="chat-box__bot-text">{{ msg.message }}</p>
          </div>

          <!-- Regular message -->
          <div v-else class="chat-box__message">
            <img
              :src="avatarUrl(msg.nickname)"
              :alt="msg.nickname"
              class="chat-box__avatar"
            />
            <div class="chat-box__message-body">
              <div class="chat-box__message-meta">
                <span class="chat-box__message-author">{{ msg.nickname }}</span>
                <time class="chat-box__message-time">{{ formatTime(msg.created_at) }}</time>
              </div>
              <p class="chat-box__message-text">{{ msg.message }}</p>
            </div>
          </div>
        </template>

        <div ref="endRef" />
      </div>
    </div>

    <!-- Night overlay -->
    <div v-if="phase === 'night'" class="chat-box__night-overlay">
      <span class="chat-box__night-icon">🌙</span>
      <p class="chat-box__night-title">The village sleeps...</p>
      <p class="chat-box__night-sub">Chat disabled during night phase</p>
    </div>

    <!-- Input -->
    <form class="chat-box__form" @submit.prevent="handleSend">
      <BaseInput
        v-model="inputValue"
        placeholder="Type a message…"
        class="chat-box__input"
        :disabled="disabled || phase === 'night'"
        aria-label="Message"
        @keydown.enter.exact.prevent="handleSend"
        @keydown.shift.enter="() => {}"
      />
      <button
        type="submit"
        class="chat-box__send"
        :disabled="!inputValue.trim() || disabled || phase === 'night'"
        aria-label="Send message"
      >
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/>
        </svg>
      </button>
    </form>
  </section>
</template>

<script setup>
import { ref, watch, nextTick } from 'vue'
import BaseInput from '@/shared/components/BaseInput.vue'

const props = defineProps({
  messages: { type: Array, default: () => [] },
  phase: { type: String, default: 'waiting' },
  disabled: { type: Boolean, default: false },
})

const emit = defineEmits(['send'])

const inputValue = ref('')
const scrollRef = ref(null)
const endRef = ref(null)

function handleSend() {
  const text = inputValue.value.trim()
  if (!text) return
  emit('send', text)
  inputValue.value = ''
  scrollToBottom()
}

function scrollToBottom() {
  nextTick(() => {
    endRef.value?.scrollIntoView({ behavior: 'smooth' })
  })
}

function formatTime(ts) {
  if (!ts) return ''
  const d = new Date(ts)
  return d.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' })
}

function avatarUrl(nickname) {
  return `https://api.dicebear.com/7.x/bottts-neutral/svg?seed=${encodeURIComponent(nickname ?? 'anon')}`
}

watch(
  () => props.messages.length,
  () => scrollToBottom(),
  { flush: 'post' }
)
</script>

<style lang="scss" scoped>
@use '@/shared/styles/variables' as *;
@use '@/shared/styles/mixins' as *;

.chat-box {
  display: flex;
  flex-direction: column;
  flex: 1;
  min-height: 0;
  position: relative;

  &__header {
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

  &__messages {
    flex: 1;
    overflow-y: auto;
    scrollbar-width: thin;
    scrollbar-color: $color-border transparent;
    position: relative;

    &::-webkit-scrollbar { width: 4px; }
    &::-webkit-scrollbar-thumb { background: $color-border; border-radius: 4px; }
  }

  &__messages-inner {
    display: flex;
    flex-direction: column;
    gap: 14px;
    padding: 16px;
  }

  &__system {
    display: flex;
    justify-content: center;
  }

  &__system-text {
    font-size: 11px;
    color: $color-muted-foreground;
    background: rgba(255, 255, 255, 0.04);
    border-radius: 999px;
    padding: 3px 12px;
    font-style: italic;
  }

  &__bot {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 10px 14px;
    background: rgba($color-primary, 0.08);
    border-left: 3px solid $color-primary;
    border-radius: 0 8px 8px 0;
  }

  &__bot-icon {
    font-size: 16px;
    flex-shrink: 0;
    margin-top: 1px;
  }

  &__bot-text {
    font-size: 13px;
    color: $color-foreground;
    font-style: italic;
    margin: 0;
    line-height: 1.5;
  }

  &__message {
    display: flex;
    align-items: flex-start;
    gap: 10px;
  }

  &__avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    border: 1px solid $color-border;
    background: $color-secondary;
    flex-shrink: 0;
  }

  &__message-body {
    min-width: 0;
    flex: 1;
  }

  &__message-meta {
    display: flex;
    align-items: baseline;
    gap: 8px;
    margin-bottom: 2px;
  }

  &__message-author {
    font-size: 13px;
    font-weight: 600;
    color: $color-foreground;
  }

  &__message-time {
    font-size: 11px;
    color: $color-muted-foreground;
  }

  &__message-text {
    font-size: 13px;
    color: rgba($color-foreground, 0.9);
    line-height: 1.55;
    margin: 0;
    word-break: break-word;
  }

  &__night-overlay {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 8px;
    background: rgba(5, 5, 20, 0.85);
    backdrop-filter: blur(4px);
    z-index: 10;
    pointer-events: none;
  }

  &__night-icon {
    font-size: 40px;
    animation: pulse 3s ease-in-out infinite;
  }

  &__night-title {
    font-size: 16px;
    font-weight: 600;
    color: $color-foreground;
    margin: 0;
  }

  &__night-sub {
    font-size: 13px;
    color: $color-muted-foreground;
    margin: 0;
  }

  &__form {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 16px;
    border-top: 1px solid $color-border;
    flex-shrink: 0;
  }

  &__input {
    flex: 1;
  }

  &__send {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 8px;
    border: none;
    background: $color-primary;
    color: $color-primary-foreground;
    cursor: pointer;
    transition: opacity 0.15s;
    flex-shrink: 0;

    &:disabled {
      opacity: 0.4;
      cursor: not-allowed;
    }

    &:not(:disabled):hover {
      opacity: 0.85;
    }
  }
}

@keyframes pulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.08); }
}
</style>
