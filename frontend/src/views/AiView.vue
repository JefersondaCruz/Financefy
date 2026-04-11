<template>
  <div class="min-h-screen bg-[#07090F] text-white flex">

    <AppSidebar :isOpen="isMenuOpen" activeItem="IA Financeira" @close="isMenuOpen = false" />

    <div class="flex-1 flex flex-col min-w-0 min-h-screen">

      <!-- Topbar -->
      <header class="flex items-center justify-between px-6 py-5 md:px-8 border-b border-[#1E2D45] shrink-0">
        <div class="flex items-center gap-4">
          <button
            class="w-10 h-10 flex flex-col items-center justify-center gap-[5px] bg-[#0D1526] border border-[#1E2D45] rounded-xl hover:border-[#4F8EF7] transition-colors"
            @click="isMenuOpen = true"
          >
            <span class="block w-4 h-[1.5px] bg-white rounded" />
            <span class="block w-4 h-[1.5px] bg-white rounded" />
            <span class="block w-4 h-[1.5px] bg-white rounded" />
          </button>
          <div>
            <div class="flex items-center gap-2">
              <h1 class="text-xl font-extrabold tracking-tight text-white leading-tight">IA Financeira</h1>
              <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-[#4F8EF7]/15 text-[#4F8EF7] tracking-widest uppercase">Beta</span>
            </div>
            <p class="text-[12px] text-[#4A6080] mt-0.5">Análise inteligente das suas finanças em tempo real</p>
          </div>
        </div>

        <button
          v-if="messages.length > 0"
          class="flex items-center gap-2 px-4 py-2 rounded-xl border border-[#1E2D45] text-[12px] font-semibold text-[#4A6080] hover:border-[#FF3D6B]/50 hover:text-[#FF3D6B] transition-all"
          @click="clearChat"
        >
          ↺ Limpar conversa
        </button>
      </header>

      <!-- Chat area -->
      <div class="flex-1 flex flex-col overflow-hidden">

        <!-- Loading history -->
      <div v-if="loadingHistory" class="flex-1 flex items-center justify-center">
        <div class="flex flex-col items-center gap-3">
          <div class="flex gap-1.5">
            <span class="w-2 h-2 rounded-full bg-[#4A6080] animate-bounce [animation-delay:0ms]" />
            <span class="w-2 h-2 rounded-full bg-[#4A6080] animate-bounce [animation-delay:150ms]" />
            <span class="w-2 h-2 rounded-full bg-[#4A6080] animate-bounce [animation-delay:300ms]" />
          </div>
          <span class="text-[12px] text-[#4A6080]">Carregando histórico...</span>
        </div>
      </div>

      <!-- Empty state / Suggestions -->
        <div v-if="!loadingHistory && messages.length === 0" class="flex-1 flex flex-col items-center justify-center px-6 py-10 gap-8">

          <!-- Icon -->
          <div class="relative">
            <div class="w-20 h-20 rounded-3xl bg-[#0D1526] border border-[#1E2D45] flex items-center justify-center text-4xl">
              ◈
            </div>
            <span class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full bg-[#00E5A0] flex items-center justify-center text-[10px] font-bold text-[#07090F]">✦</span>
          </div>

          <div class="text-center max-w-[400px]">
            <h2 class="text-xl font-bold text-white mb-2">Olá! Sou sua IA financeira</h2>
            <p class="text-[13px] text-[#4A6080] leading-relaxed">
              Tenho acesso a todos os seus dados financeiros dos últimos 3 meses.
              Pergunte sobre seus gastos, peça análises ou dicas personalizadas.
            </p>
          </div>

          <!-- Quick suggestions -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 w-full max-w-[600px]">
            <button
              v-for="s in suggestions"
              :key="s.text"
              class="flex items-start gap-3 p-4 bg-[#0D1526] border border-[#1E2D45] rounded-2xl text-left hover:border-[#4F8EF7]/50 hover:bg-[#4F8EF7]/5 transition-all group"
              @click="sendSuggestion(s.text)"
            >
              <span class="text-xl shrink-0">{{ s.icon }}</span>
              <div>
                <p class="text-[13px] font-semibold text-white group-hover:text-[#4F8EF7] transition-colors">{{ s.label }}</p>
                <p class="text-[11px] text-[#4A6080] mt-0.5">{{ s.text }}</p>
              </div>
            </button>
          </div>
        </div>

        <!-- Messages -->
        <div v-else ref="messagesEl" class="flex-1 overflow-y-auto px-6 py-6 md:px-8 flex flex-col gap-4">
          <div
            v-for="(msg, i) in messages"
            :key="i"
            class="flex gap-3"
            :class="msg.role === 'user' ? 'justify-end' : 'justify-start'"
          >
            <!-- AI avatar -->
            <div
              v-if="msg.role === 'assistant'"
              class="w-8 h-8 rounded-xl bg-[#0D1526] border border-[#1E2D45] flex items-center justify-center text-sm shrink-0 mt-0.5"
            >◈</div>

            <!-- Bubble + timestamp -->
            <div class="flex flex-col gap-1" :class="msg.role === 'user' ? 'items-end' : 'items-start'">
              <div
                class="max-w-[75%] rounded-2xl px-4 py-3 text-[13px] leading-relaxed"
                :class="msg.role === 'user'
                  ? 'bg-[#4F8EF7] text-white rounded-br-md'
                  : 'bg-[#0D1526] border border-[#1E2D45] text-[#E8EEFF] rounded-bl-md'"
              >
                <div v-html="renderMessage(msg.content)" />
              </div>
              <span v-if="msg.created_at" class="text-[10px] text-[#4A6080] px-1">
                {{ formatTime(msg.created_at) }}
              </span>
            </div>

            <!-- User avatar -->
            <div
              v-if="msg.role === 'user'"
              class="w-8 h-8 rounded-xl bg-[#4F8EF7]/20 border border-[#4F8EF7]/30 flex items-center justify-center text-sm text-[#4F8EF7] shrink-0 mt-0.5 font-bold"
            >U</div>
          </div>

          <!-- Typing indicator -->
          <div v-if="loading" class="flex gap-3 justify-start">
            <div class="w-8 h-8 rounded-xl bg-[#0D1526] border border-[#1E2D45] flex items-center justify-center text-sm shrink-0">◈</div>
            <div class="bg-[#0D1526] border border-[#1E2D45] rounded-2xl rounded-bl-md px-4 py-3 flex items-center gap-1.5">
              <span class="w-1.5 h-1.5 rounded-full bg-[#4A6080] animate-bounce [animation-delay:0ms]" />
              <span class="w-1.5 h-1.5 rounded-full bg-[#4A6080] animate-bounce [animation-delay:150ms]" />
              <span class="w-1.5 h-1.5 rounded-full bg-[#4A6080] animate-bounce [animation-delay:300ms]" />
            </div>
          </div>
        </div>

        <!-- Input bar -->
        <div class="shrink-0 px-6 py-4 md:px-8 border-t border-[#1E2D45] bg-[#07090F]">
          <!-- Quick chips (shown after first message) -->
          <div v-if="messages.length > 0" class="flex gap-2 mb-3 overflow-x-auto pb-1 scrollbar-hide">
            <button
              v-for="chip in quickChips"
              :key="chip"
              class="shrink-0 px-3 py-1.5 rounded-full border border-[#1E2D45] text-[11px] font-semibold text-[#4A6080] hover:border-[#4F8EF7]/50 hover:text-[#4F8EF7] transition-all whitespace-nowrap"
              @click="sendSuggestion(chip)"
            >{{ chip }}</button>
          </div>

          <div class="flex gap-3 items-end">
            <div class="flex-1 relative">
              <textarea
                ref="textareaEl"
                v-model="inputText"
                placeholder="Pergunte sobre suas finanças..."
                rows="1"
                class="w-full bg-[#0D1526] border border-[#1E2D45] text-white text-[13px] placeholder-[#4A6080] px-4 py-3 rounded-2xl outline-none focus:border-[#4F8EF7] transition-colors resize-none overflow-hidden leading-relaxed"
                :class="loading ? 'opacity-50 cursor-not-allowed' : ''"
                :disabled="loading"
                @keydown.enter.exact.prevent="sendMessage"
                @input="autoResize"
              />
            </div>
            <button
              class="w-11 h-11 shrink-0 rounded-2xl flex items-center justify-center text-white transition-all"
              :class="canSend
                ? 'bg-[#4F8EF7] hover:bg-[#3a7de0] hover:-translate-y-px'
                : 'bg-[#1E2D45] cursor-not-allowed opacity-50'"
              :disabled="!canSend"
              @click="sendMessage"
            >
              <span class="text-base leading-none" style="transform: rotate(90deg)">➤</span>
            </button>
          </div>
          <p class="text-[10px] text-[#4A6080] mt-2 text-center">
            A IA analisa seus dados financeiros reais dos últimos 3 meses
          </p>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, nextTick, onMounted } from 'vue'
import api from '@/services/api'
import AppSidebar from '@/components/dashboard/AppSidebar.vue'

interface Message {
  role:       'user' | 'assistant'
  content:    string
  created_at?: string
}

const isMenuOpen     = ref(false)
const inputText      = ref('')
const loading        = ref(false)
const loadingHistory = ref(true)
const messages       = ref<Message[]>([])
const messagesEl     = ref<HTMLElement | null>(null)
const textareaEl     = ref<HTMLTextAreaElement | null>(null)

const canSend = computed(() => inputText.value.trim().length > 0 && !loading.value)

const suggestions = [
  { icon: '🔍', label: 'Análise geral',         text: 'Faça uma análise completa das minhas finanças e diga onde posso melhorar.' },
  { icon: '🛡️', label: 'Reserva de emergência', text: 'Quanto devo guardar de reserva de emergência com base nos meus gastos?' },
  { icon: '📊', label: 'Onde mais gasto',        text: 'Em quais categorias estou gastando mais e o que é possível reduzir?' },
  { icon: '💡', label: 'Dicas de economia',      text: 'Me dê 3 dicas práticas para economizar baseadas nos meus gastos recentes.' },
]

const quickChips = [
  'Como está minha taxa de poupança?',
  'Quais gastos posso cortar?',
  'Estou gastando mais do que ganho?',
  'Análise dos últimos 3 meses',
  'Dicas para investir',
]

// ── Load history from DB on mount ─────────────────────────────────────────
onMounted(async () => {
  try {
    const { data } = await api.get('/ai/history')
    messages.value = data
    await scrollToBottom()
  } catch (e) {
    console.error('Erro ao carregar histórico:', e)
  } finally {
    loadingHistory.value = false
  }
})

// ── Helpers ────────────────────────────────────────────────────────────────
const scrollToBottom = async () => {
  await nextTick()
  if (messagesEl.value) messagesEl.value.scrollTop = messagesEl.value.scrollHeight
}

const autoResize = () => {
  const el = textareaEl.value
  if (!el) return
  el.style.height = 'auto'
  el.style.height = Math.min(el.scrollHeight, 140) + 'px'
}

const resetTextarea = () => {
  inputText.value = ''
  if (textareaEl.value) textareaEl.value.style.height = 'auto'
}

const sendSuggestion = (text: string) => {
  inputText.value = text
  sendMessage()
}

// ── Send message ───────────────────────────────────────────────────────────
const sendMessage = async () => {
  if (!canSend.value) return
  const text = inputText.value.trim()
  resetTextarea()

  // Optimistic update — show user message immediately
  messages.value.push({ role: 'user', content: text })
  await scrollToBottom()
  loading.value = true

  try {
    const { data } = await api.post('/ai/analyze', { message: text })
    messages.value.push({ role: 'assistant', content: data.reply })
  } catch {
    messages.value.push({
      role: 'assistant',
      content: '❌ Ocorreu um erro ao processar sua mensagem. Tente novamente em instantes.',
    })
  } finally {
    loading.value = false
    await scrollToBottom()
  }
}

// ── Clear chat ─────────────────────────────────────────────────────────────
const clearChat = async () => {
  try { await api.delete('/ai/conversation') } catch {}
  messages.value = []
}

// ── Markdown renderer ──────────────────────────────────────────────────────
const renderMessage = (text: string): string => {
  return text
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
    .replace(/\*(.*?)\*/g, '<em>$1</em>')
    .replace(/`(.*?)`/g, '<code class="bg-white/10 px-1 py-0.5 rounded text-[12px] font-mono">$1</code>')
    .replace(/\n/g, '<br />')
}

// ── Format timestamp ───────────────────────────────────────────────────────
const formatTime = (dateStr?: string): string => {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })
}
</script>
