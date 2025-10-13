import "./bootstrap"
import Alpine from "alpinejs"
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// Configure Laravel Echo with Soketi
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY || 'local',
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER || 'mt1',
    wsHost: import.meta.env.VITE_PUSHER_HOST || '127.0.0.1',
    wsPort: import.meta.env.VITE_PUSHER_PORT || 6001,
    wssPort: import.meta.env.VITE_PUSHER_PORT || 6001,
    forceTLS: false,
    encrypted: false,
    disableStats: true,
    enabledTransports: ['ws', 'wss'],
});

// Make Alpine available globally
window.Alpine = Alpine

// Start Alpine
Alpine.start()

// Import SCSS
import "../scss/app.scss"

// Navigation scroll effect
document.addEventListener("DOMContentLoaded", () => {
  const navContent = document.getElementById("nav-content")

  if (navContent) {
    window.addEventListener("scroll", () => {
      if (window.scrollY > 50) {
        navContent.classList.remove("transparent")
      } else {
        navContent.classList.add("transparent")
      }
    })
  }
})

// FAQ accordion functionality
document.addEventListener("DOMContentLoaded", () => {
  const faqItems = document.querySelectorAll(".faq-item")

  faqItems.forEach((item) => {
    const question = item.querySelector("h3")
    const answer = item.querySelector("p")

    if (question && answer) {
      // Initially hide answers
      answer.style.display = "none"

      question.addEventListener("click", () => {
        const isOpen = answer.style.display === "block"

        // Close all other FAQ items
        faqItems.forEach((otherItem) => {
          const otherAnswer = otherItem.querySelector("p")
          if (otherAnswer && otherAnswer !== answer) {
            otherAnswer.style.display = "none"
          }
        })

        // Toggle current item
        answer.style.display = isOpen ? "none" : "block"
      })
    }
  })
})

// Smooth scrolling for anchor links
document.addEventListener("DOMContentLoaded", () => {
  const links = document.querySelectorAll('a[href^="#"]')

  links.forEach((link) => {
    link.addEventListener("click", function (e) {
      const href = this.getAttribute("href")
      if (href === "#") return

      e.preventDefault()
      const target = document.querySelector(href)

      if (target) {
        target.scrollIntoView({
          behavior: "smooth",
          block: "start",
        })
      }
    })
  })
})

// Form validation
document.addEventListener("DOMContentLoaded", () => {
  const forms = document.querySelectorAll("form")

  forms.forEach((form) => {
    form.addEventListener("submit", (e) => {
      const requiredFields = form.querySelectorAll("[required]")
      let isValid = true

      requiredFields.forEach((field) => {
        if (!field.value.trim()) {
          isValid = false
          field.style.borderColor = "#dc2626"
        } else {
          field.style.borderColor = "#d1d5db"
        }
      })

      if (!isValid) {
        e.preventDefault()
        alert("Please fill in all required fields.")
      }
    })
  })
})

// Real-time broadcasting with Laravel Echo
document.addEventListener('DOMContentLoaded', function() {
    // Listen for online users updates
    if (window.Echo) {
        window.Echo.channel('online-users')
            .listen('.user.came-online', (e) => {
                console.log('User came online:', e.user);
                updateOnlineUsersDisplay(e);
            });
    }
});

// Function to update online users display
function updateOnlineUsersDisplay(event) {
    // Update admin dashboard if it exists
    const onlineUsersCard = document.querySelector('[data-online-users]');
    if (onlineUsersCard) {
        const countElement = onlineUsersCard.querySelector('.text-2xl.font-bold');
        const percentageElement = onlineUsersCard.querySelector('.text-sm.text-gray-500');

        if (countElement) {
            // This would need to be updated with actual count from server
            // For now, just log the event
            console.log('Online user event received:', event);
        }
    }
}

setInterval(() => {
    if (document.visibilityState === 'visible') {
        fetch('/ping', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });
    }
}, 300000); // 5 minutes

