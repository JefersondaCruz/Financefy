<template>
  <Transition
    enter-active-class="transition-opacity duration-200"
    enter-from-class="opacity-0"
    leave-active-class="transition-opacity duration-200"
    leave-to-class="opacity-0"
  >
    <div v-if="isOpen" class="fixed inset-0 bg-black/75 backdrop-blur-sm flex items-center justify-center z-[2000] p-4" @click="$emit('close')">
      <div class="bg-[#0D1526] border border-[#1E2D45] rounded-2xl p-8 w-full max-w-[360px] text-center shadow-2xl" @click.stop>

        <div class="w-14 h-14 rounded-full bg-[#FF3D6B]/10 border border-[#FF3D6B]/20 flex items-center justify-center text-[#FF3D6B] text-2xl mx-auto mb-5">
          ⚠
        </div>

        <h3 class="text-lg font-bold text-white mb-2">Excluir transação?</h3>
        <p class="text-[13px] text-[#4A6080] leading-relaxed mb-7">
          Esta ação não pode ser desfeita.<br />
          <span class="text-white font-semibold">{{ transactionName }}</span> será removida permanentemente.
        </p>

        <div class="flex gap-3">
          <button
            class="flex-1 py-3 rounded-xl border border-[#1E2D45] bg-white/[0.04] text-[#4A6080] text-[13px] font-bold hover:bg-white/10 hover:text-white transition-all"
            @click="$emit('close')"
          >Cancelar</button>
          <button
            class="flex-1 py-3 rounded-xl bg-[#FF3D6B] text-white text-[13px] font-bold hover:bg-[#e02050] transition-colors"
            @click="$emit('confirm')"
          >Excluir</button>
        </div>

      </div>
    </div>
  </Transition>
</template>

<script setup lang="ts">
defineProps<{ isOpen: boolean; transactionName?: string }>()
defineEmits<{ (e: 'close'): void; (e: 'confirm'): void }>()
</script>
