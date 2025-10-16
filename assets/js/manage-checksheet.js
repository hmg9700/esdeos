;(() => {
  const table = document.querySelector("#format-table tbody")
  const form = document.querySelector("#format-form")
  const addBtn = document.querySelector("#btn-add-row")
  const listHost = document.querySelector("#formats-list")

  function comboCell(name) {
    const td = document.createElement("td")
    const sel = document.createElement("select")
    sel.name = name + "[]"
    sel.className = "form-select form-select-sm"
    td.appendChild(sel)
    return { td, sel }
  }
  function textCell(name, placeholder = "") {
    const td = document.createElement("td")
    const inp = document.createElement("input")
    inp.name = name + "[]"
    inp.className = "form-control form-control-sm"
    inp.placeholder = placeholder
    td.appendChild(inp)
    return { td, inp }
  }
  function defaultCell() {
    const td = document.createElement("td")
    const sel = document.createElement("select")
    sel.name = "default_result[]"
    sel.className = "form-select form-select-sm"
    sel.innerHTML = '<option value="OK">OK</option><option value="NG">NG</option>'
    td.appendChild(sel)
    return { td, sel }
  }
  function removeCell(tr) {
    const td = document.createElement("td")
    const btn = document.createElement("button")
    btn.type = "button"
    btn.className = "btn btn-link text-danger btn-sm"
    btn.innerHTML = '<i class="fa fa-trash"></i>'
    btn.onclick = () => tr.remove()
    td.appendChild(btn)
    return td
  }

  let optionsCache = null
  async function loadMasterOptions() {
    if (optionsCache) return optionsCache
    const res = await fetch("api/master-options.php", { cache: "no-store" })
    const data = await res.json()
    optionsCache = data
    return data
  }
  function populateSelect(sel, items) {
    sel.innerHTML = ""
    for (const t of items || []) {
      const opt = document.createElement("option")
      opt.value = t.value ?? t.name ?? t
      opt.textContent = t.label ?? t.name ?? t.value ?? t
      sel.appendChild(opt)
    }
  }
  async function addRow() {
    const tr = document.createElement("tr")
    const { td: lineTd, sel: lineSel } = comboCell("line")
    const { td: processTd, sel: processSel } = comboCell("process")
    const { td: itemTd, sel: itemSel } = comboCell("item")
    const { td: specTd, inp: specInp } = textCell("spec", "Spec / Limit")
    const { td: defTd } = defaultCell()

    tr.appendChild(lineTd)
    tr.appendChild(processTd)
    tr.appendChild(itemTd)
    tr.appendChild(specTd)
    tr.appendChild(defTd)
    tr.appendChild(removeCell(tr))
    table.appendChild(tr)

    const opts = await loadMasterOptions()
    populateSelect(lineSel, opts.lines)
    populateSelect(processSel, opts.processes)
    populateSelect(itemSel, opts.items)
  }

  async function loadFormats() {
    const res = await fetch("api/format-list.php", { cache: "no-store" })
    const data = await res.json()
    const rows = data.rows || []
    const html = [
      '<table class="table table-sm"><thead><tr><th>Title</th><th>Count</th><th>Creator</th><th>Created</th><th>Action</th></tr></thead><tbody>',
    ]
    for (const r of rows) {
      html.push(`<tr>
        <td>${r.title}</td>
        <td>${r.count}</td>
        <td>${r.creator ?? ""}</td>
        <td>${r.created_at ?? ""}</td>
        <td class="text-nowrap">
          <a class="btn btn-outline-primary btn-sm" href="?page=checksheet&title=${encodeURIComponent(r.title)}">Use</a>
          <button class="btn btn-outline-danger btn-sm" data-title="${encodeURIComponent(r.title)}">Delete</button>
        </td>
      </tr>`)
    }
    html.push("</tbody></table>")
    listHost.innerHTML = html.join("")

    listHost.querySelectorAll("button[data-title]").forEach((btn) => {
      btn.addEventListener("click", async () => {
        const title = decodeURIComponent(btn.getAttribute("data-title"))
        if (!confirm(`Delete format "${title}"?`)) return
        await fetch("api/format-delete.php", {
          method: "POST",
          body: new URLSearchParams({ title }),
        })
        loadFormats()
      })
    })
  }

  addBtn?.addEventListener("click", addRow)
  form?.addEventListener("submit", async (e) => {
    e.preventDefault()
    const fd = new FormData(form)
    const res = await fetch("api/format-save.php", { method: "POST", body: fd })
    const json = await res.json().catch(() => ({}))
    if (json.ok) {
      alert("Saved format!")
      loadFormats()
    } else {
      alert(json.error || "Failed to save")
    }
  })

  addRow()
  loadFormats()
})()
