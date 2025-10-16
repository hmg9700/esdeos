document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("checksheetForm")
  const table = document.getElementById("checksheetTable")
  const btnAdd = document.getElementById("btnAddRow")
  const titleSelect = document.getElementById("formatTitle")
  const titleInput = document.getElementById("titleInput")
  const creatorInput = document.getElementById("creatorInput")
  const btnLoadFormat = document.getElementById("btnLoadFormat")
  const btnClearRows = document.getElementById("btnClearRows")

  if (!form || !table || !btnAdd) return

  const tbody = table.querySelector("tbody")
  let rowId = 0

  let optionsCache = null
  let masterNoticeInserted = false
  let formatList = null

  async function loadMasterOptions() {
    if (optionsCache) return optionsCache
    try {
      const res = await fetch("api/master-options.php", { cache: "no-store" })
      optionsCache = await res.json()
      console.log("[v0] master-options response:", optionsCache)
    } catch (e) {
      console.log("[v0] master-options fetch error:", e)
      optionsCache = { lines: [], processes: [], items: [] }
    }
    const anyEmpty = !optionsCache.lines?.length || !optionsCache.processes?.length || !optionsCache.items?.length
    if (anyEmpty && !masterNoticeInserted) {
      const alert = document.createElement("div")
      alert.className = "alert alert-info mb-3"
      alert.innerHTML = `
        <i class="fa-regular fa-circle-question me-1"></i>
        Dropdowns are empty. Please add Line, Process, and Item in <a class="alert-link" href="index.php?page=master">Data Master</a>, then refresh this page.
      `
      form.prepend(alert)
      masterNoticeInserted = true
    }
    return optionsCache
  }

  async function loadFormatList() {
    try {
      const res = await fetch("api/format-list.php", { cache: "no-store" })
      const data = await res.json()
      formatList = data.rows || []
      if (titleSelect) {
        const qsTitle = new URLSearchParams(location.search).get("title")
        for (const f of formatList) {
          const opt = document.createElement("option")
          opt.value = f.title
          opt.textContent = f.title
          if (qsTitle && qsTitle === f.title) opt.selected = true
          titleSelect.appendChild(opt)
        }
        if (qsTitle) {
          if (titleInput) titleInput.value = qsTitle
          await loadFormat(qsTitle)
        }
      }
    } catch (e) {
      console.log("[v0] format-list error:", e)
    }
  }

  function clearRows() {
    tbody.innerHTML = ""
    rowId = 0
  }

  async function loadFormat(title) {
    clearRows()
    if (!title) return
    try {
      const res = await fetch("api/format-get.php?title=" + encodeURIComponent(title), { cache: "no-store" })
      const data = await res.json()
      const rows = data.rows || []
      for (const r of rows) {
        addRow({
          line: r.line,
          process: r.process,
          item: r.item,
          spec: r.spec,
          default_result: r.default_result,
        })
      }
      if (rows.length === 0) addRow()
    } catch (e) {
      console.log("[v0] format-get error:", e)
      addRow()
    }
  }

  loadMasterOptions()
  loadFormatList()

  function addRow(prefill) {
    const i = rowId++
    const tr = document.createElement("tr")
    tr.innerHTML = `
      <td>
        <select name="line[${i}]" class="form-select form-select-sm" required>
          <option value="">Select Line</option>
        </select>
      </td>
      <td>
        <select name="process[${i}]" class="form-select form-select-sm" required>
          <option value="">Select Process</option>
        </select>
      </td>
      <td>
        <select name="item[${i}]" class="form-select form-select-sm" required>
          <option value="">Select Item</option>
        </select>
      </td>
      <td>
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" id="result_${i}">
          <label class="form-check-label small" for="result_${i}">NG / OK</label>
          <input type="hidden" name="result[${i}]" value="NG">
        </div>
      </td>
      <td><input name="spec[${i}]" class="form-control form-control-sm" placeholder="Spec / Limit"></td>
      <td><input type="file" name="photos[${i}][]" class="form-control form-control-sm" accept=".jpg,.jpeg,.png,.gif,.webp" multiple></td>
      <td class="text-end">
        <button type="button" class="btn btn-sm btn-outline-danger btn-remove"><i class="fa-solid fa-xmark"></i></button>
      </td>
      <input type="hidden" name="row_index[]" value="${i}">
    `
    tbody.appendChild(tr)

    const sw = tr.querySelector(`#result_${i}`)
    const hidden = tr.querySelector(`input[name="result[${i}]"]`)
    sw.addEventListener("change", () => {
      hidden.value = sw.checked ? "OK" : "NG"
    })

    populateCombos(tr, prefill)
  }

  btnAdd.addEventListener("click", () => addRow())
  if (!new URLSearchParams(location.search).get("title")) addRow()

  if (btnLoadFormat) {
    btnLoadFormat.addEventListener("click", () => {
      const t = titleSelect?.value?.trim()
      if (!t) return alert("Select a Title from the dropdown first.")
      if (titleInput) titleInput.value = t
      loadFormat(t)
    })
  }
  if (btnClearRows) {
    btnClearRows.addEventListener("click", () => {
      clearRows()
      addRow()
    })
  }

  tbody.addEventListener("click", (e) => {
    const btn = e.target.closest(".btn-remove")
    if (!btn) return
    btn.closest("tr")?.remove()
  })

  form.addEventListener("submit", async (e) => {
    e.preventDefault()
    const finalTitle = titleInput?.value?.trim()
    if (!finalTitle) {
      alert("Please enter a Title or select a Format and click Get Format first.")
      return
    }
    let ensureTitle = form.querySelector('input[name="title"]')
    if (!ensureTitle) {
      ensureTitle = document.createElement("input")
      ensureTitle.type = "hidden"
      ensureTitle.name = "title"
      form.appendChild(ensureTitle)
    }
    ensureTitle.value = finalTitle

    const fd = new FormData(form)
    const res = await fetch(form.action, { method: "POST", body: fd })
    let data = {}
    try {
      data = await res.json()
      console.log("[v0] result-save result:", data)
    } catch (e) {
      console.log("[v0] result-save parse error:", e)
    }
    if (data.ok) {
      alert("Saved!")
      window.location.href = "index.php?page=summary-audit"
    } else {
      alert(data.error || "Save failed")
    }
  })

  async function populateCombos(tr, prefill) {
    const opts = await loadMasterOptions()
    const lineSel = tr.querySelector('select[name^="line"]')
    const procSel = tr.querySelector('select[name^="process"]')
    const itemSel = tr.querySelector('select[name^="item"]')
    const specInp = tr.querySelector('input[name^="spec"]')
    const sw = tr.querySelector(".form-check-input")
    const hidden = tr.querySelector('input[name^="result"]')

    for (const l of opts.lines || []) {
      const o = document.createElement("option")
      o.value = l.name
      o.textContent = l.name
      lineSel?.appendChild(o)
    }
    for (const p of opts.processes || []) {
      const o = document.createElement("option")
      o.value = p.name
      o.textContent = p.name
      procSel?.appendChild(o)
    }
    for (const it of opts.items || []) {
      const o = document.createElement("option")
      o.value = it.name
      o.textContent = it.name
      itemSel?.appendChild(o)
    }
    if (prefill) {
      if (prefill.line) lineSel.value = prefill.line
      if (prefill.process) procSel.value = prefill.process
      if (prefill.item) itemSel.value = prefill.item
      if (prefill.spec) specInp.value = prefill.spec
      const ok = (prefill.default_result || "OK") === "OK"
      sw.checked = ok
      hidden.value = ok ? "OK" : "NG"
    }
  }

  titleSelect?.addEventListener("change", () => {
    const t = titleSelect.value
    if (titleInput) titleInput.value = t
    if (t) loadFormat(t)
  })
})
