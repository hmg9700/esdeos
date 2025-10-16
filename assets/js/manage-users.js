document.addEventListener("DOMContentLoaded", () => {
  const tableBody = document.querySelector("#usersTable tbody")
  const userModal = new window.bootstrap.Modal(document.getElementById("userModal"))
  const form = document.getElementById("userForm")
  const btnAdd = document.getElementById("btnAddUser")

  async function loadUsers() {
    const res = await fetch("api/users-list.php", { cache: "no-store" })
    const data = await res.json()
    tableBody.innerHTML = ""
    ;(data.users || []).forEach((u, idx) => {
      const tr = document.createElement("tr")
      tr.innerHTML = `
        <td>${idx + 1}</td>
        <td>${u.username ?? ""}</td>
        <td>${u.full_name ?? ""}</td>
        <td>${u.email}</td>
        <td><span class="badge ${u.level === "admin" ? "bg-danger" : "bg-secondary"}">${u.level}</span></td>
        <td><span class="badge ${u.status === "active" ? "bg-success" : "bg-warning text-dark"}">${u.status}</span></td>
        <td>${u.created_at ?? ""}</td>
        <td>
          <button class="btn btn-sm btn-outline-primary me-1" data-action="edit" data-id="${u.id}"><i class="fa-regular fa-pen-to-square"></i></button>
          <button class="btn btn-sm btn-outline-danger" data-action="delete" data-id="${u.id}"><i class="fa-regular fa-trash-can"></i></button>
        </td>`
      tableBody.appendChild(tr)
    })
  }

  btnAdd.addEventListener("click", () => {
    form.reset()
    form.userId && (form.userId.value = "")
    userModal.show()
  })

  tableBody.addEventListener("click", async (e) => {
    const btn = e.target.closest("button[data-action]")
    if (!btn) return
    const id = btn.getAttribute("data-id")
    if (btn.dataset.action === "edit") {
      const res = await fetch(`api/users-list.php?id=${encodeURIComponent(id)}`)
      const data = await res.json()
      const u = data.user
      if (!u) return
      form.userId.value = u.id
      form.username.value = u.username ?? ""
      form.full_name.value = u.full_name ?? ""
      form.email.value = u.email
      form.level.value = u.level ?? "user"
      form.status.value = u.status ?? "deactive"
      form.password.value = ""
      userModal.show()
    } else if (btn.dataset.action === "delete") {
      if (!confirm("Delete this user?")) return
      const fd = new FormData()
      fd.append("csrf_token", window.CSRF_TOKEN || "")
      fd.append("id", id)
      const res = await fetch("api/users-delete.php", { method: "POST", body: fd })
      const json = await res.json()
      if (!json.ok) alert(json.error || "Failed")
      await loadUsers()
    }
  })

  form.addEventListener("submit", async (e) => {
    e.preventDefault()
    const fd = new FormData(form)
    fd.set("csrf_token", window.CSRF_TOKEN || "")
    const res = await fetch("api/users-save.php", { method: "POST", body: fd })
    const json = await res.json()
    if (!json.ok) {
      alert(json.error || "Failed")
      return
    }
    userModal.hide()
    await loadUsers()
  })

  loadUsers()
})
