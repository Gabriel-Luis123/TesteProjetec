let currentContactId = null
let messageRefreshInterval = null
let pendingFile = null
console.log("Chat JS carregado")

document.addEventListener("DOMContentLoaded", () => {
  loadContacts()
  setupEventListeners()
  setupInputListeners()
})

function setupEventListeners() {
  const form = document.querySelector(".form-input")
  if (form) {
    form.addEventListener("submit", async (e) => {
      e.preventDefault()

      // Se há arquivo pendente, envia primeiro
      if (pendingFile) {
        await sendPendingFile()
        clearPreview()
      }

      // Depois envia a mensagem de texto se houver
      const textInput = document.getElementById("textInput")
      const text = textInput.value.trim()
      if (text) {
        await sendMessage()
      }
    })
  }

  document.addEventListener("click", (e) => {
    const box = e.target.closest(".caixa-nome")
    if (!box) return

    const contactId = box.dataset.id
    if (!contactId) {
      console.warn("Contato sem data-id:", box)
      return
    }

    selectContact(contactId)
  })
}

function setupInputListeners() {
  const textInput = document.getElementById("textInput")
  if (textInput) {
    // Now only the form submit handler sends messages
  }

  const searchInput = document.getElementById("searchInput")
  if (searchInput) {
    searchInput.addEventListener("input", async function () {
      const termo = this.value.trim()

      const url =
        termo.length > 0
          ? `../src/controllers/chat_backend.php?action=search_contacts&q=${encodeURIComponent(termo)}`
          : `../src/controllers/chat_backend.php?action=get_contacts`

      try {
        const response = await fetch(url)
        const data = await response.json()
        if (data.sucesso) renderContacts(data.contatos)
      } catch (error) {
        console.error("Erro ao pesquisar:", error)
      }
    })
  }

  const fileInput = document.getElementById("fileInput")
  if (fileInput) {
    fileInput.addEventListener("change", function () {
      const file = this.files[0]
      const wrapper = document.querySelector(".input-wrapper")
      const previewBox = document.getElementById("filePreview")

      if (!file) {
        pendingFile = null
        if (previewBox) previewBox.style.display = "none"
        if (wrapper) wrapper.classList.remove("preview-active")
        return
      }

      if (!file.type.startsWith("image/")) {
        alert("Apenas imagens são permitidas!")
        this.value = ""
        return
      }

      pendingFile = file
      if (previewBox) previewBox.innerHTML = ""

      const previewElement = document.createElement("img")
      previewElement.src = URL.createObjectURL(file)
      previewElement.classList.add("preview-image")

      const removeBtn = document.createElement("span")
      removeBtn.textContent = "✖"
      removeBtn.classList.add("remove-file")
      removeBtn.onclick = () => {
        pendingFile = null
        previewBox.style.display = "none"
        previewBox.innerHTML = ""
        fileInput.value = ""
        wrapper.classList.remove("preview-active")
        adjustInputPadding()
      }

      previewBox.appendChild(previewElement)
      previewBox.appendChild(removeBtn)
      previewBox.style.display = "flex"

      wrapper.classList.add("preview-active")
      setTimeout(adjustInputPadding, 20)
    })
  }
}

async function loadContacts() {
  try {
    const response = await fetch("../src/controllers/chat_backend.php?action=get_contacts")
    const data = await response.json()
    if (data.sucesso && data.contatos) renderContacts(data.contatos)
  } catch (error) {
    console.error("Erro ao carregar contatos:", error)
  }
}

function renderContacts(contatos) {
  const container = document.querySelector(".lista-nomes")
  if (!container) return

  container.innerHTML = ""

  contatos.forEach((contato) => {
    const caixa = document.createElement("div")
    caixa.className = "caixa-nome"

    const idValue =
      contato.id ??
      contato.ID ??
      contato.id_contato ??
      contato.user_id ??
      contato.contato_id ??
      contato.Registro_Academico ??
      contato.ra ??
      contato.registro ??
      null

    caixa.dataset.id = idValue ? String(idValue) : ""

    if (String(currentContactId) === String(idValue)) {
      caixa.classList.add("selecionado")

      console.log("Contato recebido:", contato)
    }

    const colorClass = getColorForDiscipline(contato.disciplina)
    const disciplineName = extractDisciplineName(contato.disciplina)
    const foto = contato.Foto_Perfil || "../public/img/fotosPerfil/perfilPadrao.png"
    const nome = contato.Nome || "Sem nome"

    caixa.innerHTML = `
      <div class="${colorClass}">
        <div>
          <h1 class="nome-monitor">${escapeHtml(nome)}</h1>
          <p class="titulo-monitor-caixa-nome">${escapeHtml(disciplineName)}</p>
        </div>
      </div>
      <div class="icone-contato" 
           style="background-image: url('${foto}'); 
                  background-size: cover; 
                  background-position: center; 
                  background-repeat: no-repeat; 
                  border-radius: 50%;">
      </div>
    `

    container.appendChild(caixa)
  })
}

function appendMessage(msg) {
  const container = document.querySelector(".mensagens-container")
  if (!container) return

  const isMe = Number(msg.remetente_id) === Number(getCurrentUserId())
  const msgClass = isMe ? "direita" : "esquerda"

  let content = ""

  if (msg.arquivo_url) {
    const ext = msg.arquivo_url.split(".").pop().toLowerCase()
    if (["jpg", "jpeg", "png", "gif", "webp"].includes(ext)) {
      content = `<img src="${msg.arquivo_url}" class="img-preview">`
    } else {
      content = `<a href="${msg.arquivo_url}" download>📎 Arquivo</a>`
    }
  } else {
    content = `<p>${escapeHtml(msg.conteudo || "")}</p>`
  }

  const html = `
      <div class="mensagens-geral ${msgClass}">
        ${content}
        <p class="hora">${escapeHtml(formatTime(msg.data_hora))}</p>
      </div>
  `

  container.insertAdjacentHTML("beforeend", html)
  container.scrollTop = container.scrollHeight
}

async function selectContact(id) {
  try {
    stopMessageRefresh()

    const resp = await fetch(`../src/controllers/chat_backend.php?action=get_contact_details&id=${id}`)

    if (!resp.ok) throw new Error("Erro get_contact_details")

    const data = await resp.json()

    console.log("Resposta bruta get_contact_details:", data)

    const contato = data.contato

    updateHeaderContact(contato)

    currentContactId = contato.id

    await loadMessages(currentContactId)

    console.log("[v0] Starting message refresh for contact:", currentContactId)
    startMessageRefresh()
  } catch (err) {
    console.error("Erro ao obter detalhes do contato:", err)
  }
}

function startMessageRefresh() {
  if (messageRefreshInterval) {
    clearInterval(messageRefreshInterval)
  }

  messageRefreshInterval = setInterval(() => {
    if (currentContactId) {
      console.log("[v0] Refreshing messages for contact:", currentContactId)
      refreshMessages()
    }
  }, 3000)
}

function stopMessageRefresh() {
  if (messageRefreshInterval) {
    console.log("[v0] Stopping message refresh")
    clearInterval(messageRefreshInterval)
    messageRefreshInterval = null
  }
}

async function refreshMessages() {
  if (!currentContactId) return

  try {
    const formData = new FormData()
    formData.append("contact_id", currentContactId)

    const response = await fetch("../src/controllers/chat_backend.php?action=get_messages", {
      method: "POST",
      body: formData,
    })

    const data = await response.json()
    if (data.sucesso) {
      const container = document.querySelector(".mensagens-container")
      const currentMessages = container.querySelectorAll(".mensagens-geral")

      if (currentMessages.length !== data.mensagens.length) {
        console.log("[v0] New messages detected, updating chat")
        const scrollAtBottom = Math.abs(container.scrollHeight - container.scrollTop - container.clientHeight) < 50
        renderMessages(data.mensagens)

        // Mantém o scroll no final se já estava lá
        if (scrollAtBottom) {
          container.scrollTop = container.scrollHeight
        }
      }
    }
  } catch (error) {
    console.error("Erro ao atualizar mensagens:", error)
  }
}

function updateHeaderContact(contato) {
  const header = document.querySelector(".cabecalho-fundo")
  if (!header || !contato) return

  const nome = contato.Nome ?? "Sem nome"

  const foto = contato.Foto_Perfil || "../public/img/fotosPerfil/perfilPadrao.png"

  const disciplineName = extractDisciplineName(contato.disciplina)
  const textoUsuario = disciplineName === "Aluno" ? "Aluno" : `Monitor de ${disciplineName}`

  header.innerHTML = `
        <div class="icone-cabecalho" style="background-image: url('${foto}'); background-size: cover; background-repeat: no-repeat; border-radius: 50%;"></div>
        <div class="nome-titulo-monitor">
            <h2>${escapeHtml(nome)}</h2>
            <p class="titulo-monitor-cabecalho">${escapeHtml(textoUsuario)}</p>
        </div>
    `
}

async function loadMessages(contactId) {
  try {
    const formData = new FormData()
    formData.append("contact_id", contactId)

    const response = await fetch("../src/controllers/chat_backend.php?action=get_messages", {
      method: "POST",
      body: formData,
    })

    const data = await response.json()
    if (data.sucesso) renderMessages(data.mensagens)
  } catch (error) {
    console.error("Erro ao carregar mensagens:", error)
  }
}

function formatFullDate(timestamp) {
  const date = new Date(timestamp)
  if (isNaN(date)) return ""
  const months = [
    "janeiro",
    "fevereiro",
    "março",
    "abril",
    "maio",
    "junho",
    "julho",
    "agosto",
    "setembro",
    "outubro",
    "novembro",
    "dezembro",
  ]
  return `${date.getDate()} de ${months[date.getMonth()]}`
}

function formatTime(timestamp) {
  const date = new Date(timestamp)
  if (isNaN(date)) return ""
  return `${String(date.getHours()).padStart(2, "0")}:${String(date.getMinutes()).padStart(2, "0")}`
}

function renderMessages(mensagens) {
  const container = document.querySelector(".mensagens-container")
  if (!container) return

  let newHTML = ""
  let lastDate = null

  mensagens.forEach((msg) => {
    const isMe = Number(msg.remetente_id) === Number(getCurrentUserId())
    const msgClass = isMe ? "direita" : "esquerda"

    const dateFormatted = msg.data_formatada || formatFullDate(msg.data_hora)

    if (dateFormatted !== lastDate) {
      newHTML += `<div class="chat-date">${escapeHtml(dateFormatted)}</div>`
      lastDate = dateFormatted
    }

    let content = ""

    if (msg.arquivo_url) {
      const ext = msg.arquivo_url.split(".").pop().toLowerCase()
      if (["jpg", "jpeg", "png", "gif", "webp"].includes(ext)) {
        content = `<img src="${msg.arquivo_url}" class="img-preview">`
      } else {
        content = `<a href="${msg.arquivo_url}" download>📎 Arquivo</a>`
      }
    } else {
      content = `<p>${escapeHtml(msg.conteudo)}</p>`
    }

    newHTML += `
      <div class="mensagens-geral ${msgClass}">
        ${content}
        <p class="hora">${escapeHtml(formatTime(msg.data_hora))}</p>
      </div>
    `
  })

  container.innerHTML = newHTML
  container.scrollTop = container.scrollHeight
}

async function sendMessage() {
  if (!currentContactId) return alert("Selecione um contato")

  const input = document.getElementById("textInput")
  const text = input.value.trim()

  if (!text) return

  const formData = new FormData()
  formData.append("contact_id", currentContactId)
  formData.append("mensagem", text)

  try {
    const response = await fetch("../src/controllers/chat_backend.php?action=send_message", {
      method: "POST",
      body: formData,
    })

    const data = await response.json()

    if (data.sucesso) {
      if (data.mensagem) {
        appendMessage(data.mensagem)
      } else {
        const tempMsg = {
          remetente_id: getCurrentUserId(),
          destinatario_id: currentContactId,
          conteudo: text,
          arquivo_url: null,
          data_hora: new Date().toISOString(),
        }
        appendMessage(tempMsg)
      }
      input.value = ""
    } else {
      console.error("Erro ao enviar mensagem:", data)
      alert(data.erro || "Erro ao enviar mensagem")
    }
  } catch (error) {
    console.error("Erro ao enviar mensagem:", error)
    alert("Erro ao enviar mensagem")
  }
}

async function sendPendingFile() {
  if (!pendingFile || !currentContactId) return

  const formData = new FormData()
  formData.append("contact_id", currentContactId)
  formData.append("file", pendingFile)

  try {
    const response = await fetch("../src/controllers/chat_backend.php?action=send_file", {
      method: "POST",
      body: formData,
    })

    const data = await response.json()

    if (data.sucesso) {
      if (data.mensagem) {
        appendMessage(data.mensagem)
      } else if (data.file_url) {
        const tempMsg = {
          remetente_id: getCurrentUserId(),
          destinatario_id: currentContactId,
          conteudo: null,
          arquivo_url: data.file_url,
          data_hora: new Date().toISOString(),
        }
        appendMessage(tempMsg)
      }
      clearPreview()
    } else {
      console.error("Erro ao enviar arquivo:", data)
      alert(data.erro || "Erro ao enviar arquivo")
    }
  } catch (error) {
    console.error("Erro ao enviar arquivo:", error)
    alert("Erro ao enviar arquivo")
  }
}

function getCurrentUserId() {
  return window.CurrentUserId
}

function getColorForDiscipline(disciplina) {
  if (!disciplina) return "caixa-aluno"

  const d = disciplina.toLowerCase()
  if (d.includes("matemática")) return "caixa-matematica"
  if (d.includes("português")) return "caixa-portugues"
  if (d.includes("história")) return "caixa-historia"
  if (d.includes("eletrônica")) return "caixa-elet-analogica"
  if (d.includes("biologia")) return "caixa-biologia"
  if (d.includes("química")) return "caixa-quimica"
  if (d.includes("física")) return "caixa-fisica"
  if (d.includes("filosofia")) return "caixa-filosofia"
  if (d.includes("web")) return "caixa-WEB"
  if (d.includes("geografia")) return "caixa-geografia"
  if (d.includes("inglês")) return "caixa-ingles"
  if (d.includes("artes")) return "caixa-artes"
  if (d.includes("sociologia")) return "caixa-sociologia"
  if (d.includes("geografafia")) return "caixa-geografia"
  if (d.includes("banco")) return "caixa-banco-de-dados"
  return "caixa-aluno"
}

function extractDisciplineName(disciplina) {
  if (!disciplina) return "Aluno"
  return String(disciplina).split("-")[0].trim()
}

function adjustInputPadding() {
  const input = document.getElementById("textInput")
  const wrapper = document.querySelector(".input-wrapper")

  if (wrapper.classList.contains("preview-active")) {
    input.style.paddingLeft = "120px"
  } else {
    input.style.paddingLeft = "15px"
  }
}

function clearPreview() {
  pendingFile = null

  const previewBox = document.getElementById("filePreview")
  const wrapper = document.querySelector(".input-wrapper")
  const fileInputEl = document.getElementById("fileInput")

  if (previewBox) {
    previewBox.style.display = "none"
    previewBox.innerHTML = ""
  }

  if (fileInputEl) fileInputEl.value = ""

  if (wrapper) wrapper.classList.remove("preview-active")

  adjustInputPadding()
}

function escapeHtml(text) {
  text = text == null ? "" : String(text)
  const map = {
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    '"': "&quot;",
    "'": "&#039;",
  }
  return text.replace(/[&<>"']/g, (m) => map[m])
}
