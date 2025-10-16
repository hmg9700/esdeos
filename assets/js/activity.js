document.addEventListener("DOMContentLoaded", () => {
  const calEl = document.getElementById("activity-calendar")
  const FullCalendar = window.FullCalendar // Declare the FullCalendar variable

  const hasCalendar = !!(calEl && FullCalendar)

  const loggedIn = calEl?.getAttribute("data-logged-in") === "1"
  const modalEl = document.getElementById("activityModal")
  const modal = window.bootstrap ? new window.bootstrap.Modal(modalEl) : null
  const modalForm = document.getElementById("activity-modal-form")
  const csrf = modalForm?.querySelector('input[name="csrf"]')?.value || ""

  const setVal = (selector, value) => {
    const el = modalForm ? modalForm.querySelector(selector) : null
    if (el) el.value = value || ""
  }

  function renderFiles(files = []) {
    const tbody = document.getElementById("am-files-body")
    if (!tbody) return
    tbody.innerHTML = ""
    files.forEach((f) => {
      const tr = document.createElement("tr")
      tr.innerHTML = `
        <td>${(f.description || "").replace(/</g, "&lt;")}</td>
        <td><a href="uploads/activity/${encodeURIComponent(f.filename)}" target="_blank" rel="noopener">${f.filename}</a></td>
        <td>${f.size ? Math.round(f.size / 1024) + " KB" : "-"}</td>
        <td>
          ${
            loggedIn
              ? `<button type="button" class="btn btn-sm btn-outline-danger am-file-del" data-id="${f.id}">
            <i class="fa-solid fa-trash"></i></button>`
              : "-"
          }
        </td>
      `
      tbody.appendChild(tr)
    })
  }

  async function refreshFiles(activityId) {
    if (!activityId) return renderFiles([])
    try {
      const res = await fetch(`api/activity-get.php?id=${encodeURIComponent(activityId)}`, { cache: "no-store" })
      const data = await res.json()
      renderFiles(data.files || [])
    } catch {
      renderFiles([])
    }
  }

  let calendar = null
  if (hasCalendar) {
    calendar = new FullCalendar.Calendar(calEl, {
      initialView: "dayGridMonth",
      headerToolbar: {
        left: "prev,next today",
        center: "title",
        right: "dayGridMonth,timeGridWeek,timeGridDay,listMonth",
      },
      navLinks: true,
      selectable: true,
      events: "api/activity-events.php",
      eventTimeFormat: { hour: "2-digit", minute: "2-digit", meridiem: false },
      dateClick: (info) => {
        if (!loggedIn) return
        modalForm?.reset()
        setVal("#am-id", "")
        setVal("#am-start-date", info.dateStr)
        setVal("#am-start-time", "09:00")
        renderFiles([])
        modal?.show()
      },
      eventClick: async (arg) => {
        const id = arg.event.id
        if (!id) return
        try {
          const res = await fetch(`api/activity-get.php?id=${encodeURIComponent(id)}`, { cache: "no-store" })
          const data = await res.json()
          setVal("#am-id", data.id || id)
          setVal("#am-title", data.title)
          setVal("#am-pic", data.pic)
          const [sd, stRaw] = (data.start_at || "").split(" ")
          setVal("#am-start-date", sd)
          setVal("#am-start-time", (stRaw || "").slice(0, 5))
          const [ed, etRaw] = (data.end_at || "").split(" ")
          setVal("#am-end-date", ed)
          setVal("#am-end-time", (etRaw || "").slice(0, 5))
          setVal("#am-location", data.location)
          setVal("#am-notes", data.notes)
          renderFiles(data.files || [])
          modal?.show()
        } catch {
          alert("Failed to load activity details")
        }
      },
    })
    calendar.render()
  }

  const addBtn = document.getElementById("activity-add-btn")
  if (addBtn) {
    addBtn.addEventListener("click", () => {
      if (!loggedIn) {
        alert("Login required to add activities.")
        return
      }
      modalForm?.reset()
      setVal("#am-id", "")
      renderFiles([])
      modal?.show()
    })
  }

  if (modalForm) {
    modalForm.addEventListener("submit", async (e) => {
      e.preventDefault()
      const fd = new FormData(modalForm)
      const id = fd.get("id")
      const url = id ? "api/activity-update.php" : "api/activity-create.php"
      const res = await fetch(url, { method: "POST", body: fd })
      try {
        const data = await res.json()
        if (data.ok) {
          if (!id && data.id) {
            modalForm.querySelector("#am-id").value = data.id
          }
          calendar?.refetchEvents()
          alert("Saved")
        } else {
          alert(data.error || "Failed to save")
        }
      } catch {
        alert("Failed to save")
      }
    })
  }

  const delBtn = document.getElementById("activity-delete-btn")
  if (delBtn) {
    delBtn.addEventListener("click", async () => {
      const id = modalForm?.querySelector("#am-id")?.value
      if (!id) return
      if (!confirm("Delete this activity?")) return
      const fd = new FormData()
      fd.append("csrf", csrf)
      fd.append("id", id)
      const res = await fetch("api/activity-delete.php", { method: "POST", body: fd })
      try {
        const data = await res.json()
        if (data.ok) {
          modal && window.bootstrap?.Modal.getInstance(modalEl)?.hide()
          calendar?.refetchEvents()
        } else {
          alert(data.error || "Failed to delete")
        }
      } catch {
        alert("Failed to delete")
      }
    })
  }

  const addFileBtn = document.getElementById("am-add-file-btn")
  if (addFileBtn) {
    addFileBtn.addEventListener("click", () => {
      if (!loggedIn) return alert("Login required")
      const tbody = document.getElementById("am-files-body")
      if (!tbody) return
      const tr = document.createElement("tr")
      tr.innerHTML = `
        <td><input type="text" class="form-control form-control-sm am-file-desc" placeholder="Description"></td>
        <td><input type="file" class="form-control form-control-sm am-file-input"></td>
        <td class="text-muted">-</td>
        <td><button type="button" class="btn btn-sm btn-outline-primary am-file-upload">
          <i class="fa-solid fa-upload"></i></button></td>
      `
      tbody.prepend(tr)
    })
  }

  document.getElementById("am-files-body")?.addEventListener("click", async (e) => {
    const target = e.target.closest("button")
    if (!target) return
    const id = modalForm?.querySelector("#am-id")?.value
    if (target.classList.contains("am-file-upload")) {
      const row = target.closest("tr")
      const fInput = row.querySelector(".am-file-input")
      const dInput = row.querySelector(".am-file-desc")
      if (!id) return alert("Save the activity first.")
      if (!fInput?.files?.[0]) return alert("Choose a file.")
      const fd = new FormData()
      fd.append("csrf", csrf)
      fd.append("activity_id", id)
      fd.append("description", dInput?.value || "")
      fd.append("file", fInput.files[0])
      const res = await fetch("api/activity-file-upload.php", { method: "POST", body: fd })
      try {
        const data = await res.json()
        if (data.ok) {
          await refreshFiles(id)
          row.remove()
        } else {
          alert(data.error || "Upload failed")
        }
      } catch {
        alert("Upload failed")
      }
    }
    if (target.classList.contains("am-file-del")) {
      if (!confirm("Delete this file?")) return
      const fileId = target.getAttribute("data-id")
      const fd = new FormData()
      fd.append("csrf", csrf)
      fd.append("id", fileId)
      const res = await fetch("api/activity-file-delete.php", { method: "POST", body: fd })
      try {
        const data = await res.json()
        if (data.ok) {
          await refreshFiles(id)
        } else {
          alert(data.error || "Delete failed")
        }
      } catch {
        alert("Delete failed")
      }
    }
  })
})
