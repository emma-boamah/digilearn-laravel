import "./bootstrap"
import Alpine from "alpinejs"

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

