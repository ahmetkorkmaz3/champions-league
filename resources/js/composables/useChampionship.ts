import { computed, watch, type Ref } from 'vue'
import type { Standing, Match } from '@/types/champions-league'
import { useConfetti } from './useConfetti'

export function useChampionship(standings: Ref<Standing[]>, matches: Ref<Match[]>) {
  const { triggerChampionshipConfetti } = useConfetti()

  const champion = computed(() => {
    if (!standings.value || standings.value.length === 0) return null
    return standings.value.find(standing => standing.position === 1) || null
  })

  const isChampionshipDecided = computed(() => {
    if (!matches.value || matches.value.length === 0) return false
    
    // Tüm maçların oynanıp oynanmadığını kontrol et
    const totalMatches = matches.value.length
    const playedMatches = matches.value.filter(match => match.is_played).length
    
    console.log('Total matches:', totalMatches, 'Played matches:', playedMatches)
    return totalMatches > 0 && playedMatches === totalMatches
  })

  const shouldShowChampionshipCelebration = computed(() => {
    return isChampionshipDecided.value && champion.value !== null
  })

  // Şampiyon belirlendiğinde confetti patlat
  watch(shouldShowChampionshipCelebration, (newValue) => {
    if (newValue) {
      // Kısa bir gecikme ile confetti patlat
      setTimeout(() => {
        triggerChampionshipConfetti()
      }, 500)
    }
  })

  return {
    champion,
    isChampionshipDecided,
    shouldShowChampionshipCelebration
  }
} 