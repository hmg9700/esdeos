// Import FullCalendar library
const FullCalendar = window.FullCalendar // Declare FullCalendar variable

document.addEventListener("DOMContentLoaded", () => {
  if (!FullCalendar || !document.getElementById("calendar")) return

  const calEl = document.getElementById("calendar")
  const loggedIn = calEl.getAttribute("data-logged-in") === "1"

  const calendar = new FullCalendar.Calendar(calEl, {
    initialView: "dayGridMonth",
    headerToolbar: {
      left: "prev,next today",
      center: "title",
      right: "dayGridMonth,timeGridWeek,timeGridDay,listMonth",
    },
    navLinks: true,
    selectable: true,
    events: "api/events.php",
    eventTimeFormat: { hour: "2-digit", minute: "2-digit", meridiem: false },
    dateClick: (info) => {
      if (!loggedIn) {
        alert("Please login to add schedules.")
        return
      }
      const dateInput = document.querySelector('form[action="api/schedule_create.php"] input[name="scheduled_date"]')
      const titleInput = document.querySelector('form[action="api/schedule_create.php"] input[name="task_name"]')
      if (dateInput) dateInput.value = info.dateStr
      if (titleInput) titleInput.focus()
      // scroll to the form for better UX
      const formEl = document.querySelector('form[action="api/schedule_create.php"]')
      if (formEl) formEl.scrollIntoView({ behavior: "smooth", block: "start" })
    },
    eventClick: async (arg) => {
      if (!loggedIn) return
      const ok = confirm(`Delete "${arg.event.title}"?`)
      if (!ok) return

      const fd = new FormData()
      fd.append("csrf", window.CSRF_TOKEN || "")
      fd.append("id", arg.event.id)
      const res = await fetch("api/event-delete.php", { method: "POST", body: fd })
      try {
        const data = await res.json()
        if (data.ok) arg.event.remove()
        else alert(data.error || "Failed to delete")
      } catch {
        alert("Failed to delete")
      }
    },
  })

  calendar.render()
})
