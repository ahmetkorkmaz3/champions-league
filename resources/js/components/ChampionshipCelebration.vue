<template>
  <Transition
    enter-active-class="transition duration-1000 ease-out"
    enter-from-class="transform scale-95 opacity-0"
    enter-to-class="transform scale-100 opacity-100"
    leave-active-class="transition duration-300 ease-in"
    leave-from-class="transform scale-100 opacity-100"
    leave-to-class="transform scale-95 opacity-0"
  >
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
      <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md mx-4 text-center relative overflow-hidden">
        <!-- Confetti overlay -->
        <div class="absolute inset-0 pointer-events-none">
          <div class="absolute top-0 left-1/4 w-2 h-2 bg-yellow-400 rounded-full animate-bounce" style="animation-delay: 0s;"></div>
          <div class="absolute top-0 left-1/2 w-2 h-2 bg-blue-400 rounded-full animate-bounce" style="animation-delay: 0.2s;"></div>
          <div class="absolute top-0 left-3/4 w-2 h-2 bg-red-400 rounded-full animate-bounce" style="animation-delay: 0.4s;"></div>
          <div class="absolute top-4 left-1/3 w-2 h-2 bg-green-400 rounded-full animate-bounce" style="animation-delay: 0.6s;"></div>
          <div class="absolute top-4 right-1/3 w-2 h-2 bg-purple-400 rounded-full animate-bounce" style="animation-delay: 0.8s;"></div>
        </div>

        <!-- Trophy icon -->
        <div class="text-6xl mb-4 animate-pulse">🏆</div>

        <!-- Championship title -->
        <h2 class="text-2xl font-bold text-gray-900 mb-4">
          ŞAMPİYON!
        </h2>

        <!-- Team info -->
        <div v-if="champion" class="mb-6">
          <div class="flex items-center justify-center mb-4">
            <div class="w-16 h-16 rounded-full bg-gray-200 flex items-center justify-center mr-4">
              <img 
                :src="champion.team.logo" 
                :alt="champion.team.name" 
                class="w-full h-full object-cover rounded-full"
              />
            </div>
            <div class="text-left">
              <h3 class="text-xl font-semibold text-gray-900">
                {{ champion.team.name }}
              </h3>
              <p class="text-gray-600">{{ champion.team.city }}</p>
            </div>
          </div>

          <!-- Championship stats -->
          <div class="bg-gradient-to-r from-yellow-100 to-yellow-200 rounded-lg p-4 mb-4">
            <div class="grid grid-cols-3 gap-4 text-center">
              <div>
                <div class="text-lg font-bold text-yellow-800">{{ champion.points }}</div>
                <div class="text-xs text-yellow-700">Puan</div>
              </div>
              <div>
                <div class="text-lg font-bold text-yellow-800">{{ champion.wins }}</div>
                <div class="text-xs text-yellow-700">Galibiyet</div>
              </div>
              <div>
                <div class="text-lg font-bold text-yellow-800">{{ champion.goals_for }}</div>
                <div class="text-xs text-yellow-700">Gol</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Celebration message -->
        <p class="text-gray-600 mb-6">
          Tüm maçlar tamamlandı ve şampiyon belirlendi!
        </p>

        <!-- Close button -->
        <button
          @click="$emit('close')"
          class="bg-gradient-to-r from-yellow-400 to-yellow-600 hover:from-yellow-500 hover:to-yellow-700 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-200 transform hover:scale-105"
        >
          Kutlamayı Kapat
        </button>
      </div>
    </div>
  </Transition>
</template>

<script setup lang="ts">
import type { Standing } from '@/types/champions-league'

interface Props {
  show: boolean
  champion: Standing | null
}

defineProps<Props>()
defineEmits<{
  close: []
}>()
</script>

<style scoped>
@keyframes bounce {
  0%, 20%, 53%, 80%, 100% {
    transform: translate3d(0,0,0);
  }
  40%, 43% {
    transform: translate3d(0, -30px, 0);
  }
  70% {
    transform: translate3d(0, -15px, 0);
  }
  90% {
    transform: translate3d(0, -4px, 0);
  }
}

.animate-bounce {
  animation: bounce 1s infinite;
}
</style> 