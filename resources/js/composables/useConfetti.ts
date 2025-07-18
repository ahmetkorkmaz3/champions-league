import confetti from 'canvas-confetti'

export function useConfetti() {
  const triggerConfetti = () => {
    // Confetti patlatma fonksiyonu
    confetti({
      particleCount: 200,
      spread: 90,
      origin: { y: 0.6 }
    })
  }

  const triggerChampionshipConfetti = () => {
    // Şampiyonluk için özel confetti
    const duration = 5 * 1000 // 5 saniye sürsün
    const animationEnd = Date.now() + duration

    const interval = setInterval(function() {
      const timeLeft = animationEnd - Date.now()

      if (timeLeft <= 0) {
        return clearInterval(interval)
      }

      const colors = ['#ffd700', '#ff6b6b', '#4ecdc4', '#45b7d1', '#96ceb4', '#feca57', '#ff9ff3', '#54a0ff'];

      // Sol taraftan
      confetti({
        particleCount: 50,
        angle: 60,
        spread: 55,
        origin: { x: 0 },
        colors: colors,
        startVelocity: 45
      });
      
      // Sağ taraftan
      confetti({
        particleCount: 50,
        angle: 120,
        spread: 55,
        origin: { x: 1 },
        colors: colors,
        startVelocity: 45
      });
      
      // Ortadan
      confetti({
        particleCount: 30,
        spread: 360,
        origin: { x: 0.5, y: 0.3 },
        colors: colors,
        startVelocity: 30
      });
    }, 150) // Daha sık patlat
  }

  return {
    triggerConfetti,
    triggerChampionshipConfetti
  }
} 