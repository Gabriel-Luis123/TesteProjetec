let currentContactId = null
let messageRefreshInterval = null
let selectedFile = null
console.log("Chat JS carregado")

document.addEventListener("DOMContentLoaded", () => {
  loadContacts()
  setupEventListeners()
})

function setupEventListeners() {
  const form = document.querySelector(".form-input")
  if (form) {
    form.addEventListener("submit", (e) => {
      e.preventDefault()
      sendMessage()
    })
  }

  document.addEventListener("click", (e) => {
    if (e.target.closest(".contato-item")) {
      const contactBox = e.target.closest(".contato-item")
      const contactId = contactBox.dataset.id
      selectContact(contactId)
    }
  })

  const fileInput = document.getElementById("fileInput")
  if (fileInput) {
    fileInput.addEventListener("change", handleFileSelect)
  }
}

async function loadContacts() {
  try {
    const response = await fetch("../src/controllers/chat_backend.php?action=get_contacts")
    const data = await response.json()

    if (data.sucesso && data.contatos) {
      renderContacts(data.contatos)
    }
  } catch (error) {
    console.error("[v0] Erro ao carregar contatos:", error)
  }
}

function renderContacts(contatos) {
  const container = document.querySelector(".lista-nomes")
  if (!container) return

  container.innerHTML = ""

  contatos.forEach((contato) => {
    const contatoItem = document.createElement("div")
    contatoItem.className = "contato-item"
    contatoItem.dataset.id = contato.id

    const caixa = document.createElement("div")
    caixa.className = "caixa-nome"

    const colorClass = getColorForDiscipline(contato.disciplina)
    const disciplineName = extractDisciplineName(contato.disciplina)
    const foto = contato.Foto_Perfil || "../public/img/fotosPerfil/perfilPadrao.png"

    caixa.innerHTML = `
            <div class="${colorClass}">
                <div>
                    <h1 class="nome-monitor">${contato.Nome}</h1>
                    <p class="titulo-monitor-caixa-nome">${disciplineName}</p>
                </div>
            </div>
            <div class="icone-contato" style="background-image: url('${foto}'); background-size: cover; background-position: center; background-repeat: no-repeat; border-radius: 50%;" alt="Foto de ${contato.Nome}"></div>
        `

    contatoItem.appendChild(caixa)
    container.appendChild(contatoItem)
  })
}

async function selectContact(contactId) {
  currentContactId = contactId

  if (messageRefreshInterval) {
    clearInterval(messageRefreshInterval)
  }

  try {
    const detailsFormData = new FormData()
    detailsFormData.append("contact_id", contactId)

    const detailsResponse = await fetch("../src/controllers/chat_backend.php?action=get_contact_details", {
      method: "POST",
      body: detailsFormData,
    })
    const detailsData = await detailsResponse.json()

    if (detailsData.sucesso) {
      updateHeaderContact(detailsData.contato)
    }

    loadMessages(contactId)

    messageRefreshInterval = setInterval(() => {
      loadMessages(contactId)
    }, 2000)
  } catch (error) {
    console.error("[v0] Erro ao selecionar contato:", error)
  }
}

function updateHeaderContact(contato) {
  const header = document.querySelector(".cabecalho-fundo")
  const foto = contato.Foto_Perfil || "../public/img/fotosPerfil/perfilPadrao.png"
  const disciplineName = extractDisciplineName(contato.disciplina)
  const textoUsuario = disciplineName == "Aluno" ? "Aluno" : `Monitor de ${disciplineName}`

  header.innerHTML = `
        <div class="icone-cabecalho" style="background-image: url('${foto}'); background-size: cover; background-repeat: no-repeat; border-radius: 50%;" alt="Foto de ${contato.Nome}"></div>
        <div class="nome-titulo-monitor">
            <h2>${contato.Nome}</h2>
            <p class="titulo-monitor-cabecalho">${textoUsuario}</p>
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

    if (data.sucesso && data.mensagens) {
      renderMessages(data.mensagens, contactId)
    }
  } catch (error) {
    console.error("[v0] Erro ao carregar mensagens:", error)
  }
}

function formatFullDate(timestamp) {
  const date = new Date(timestamp)

  const day = date.getDate()

  const monthNames = [
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

  const month = monthNames[date.getMonth()]

  return `${day} de ${month}`
}

function renderMessages(mensagens, contactId) {
  const container = document.querySelector(".mensagens-container")
  if (!container) return

  let newHTML = ""
  let lastDate = null

  mensagens.forEach((msg) => {
    const isCurrentUserSender = msg.remetente_id === getCurrentUserId()
    const messageClass = isCurrentUserSender ? "direita" : "esquerda"

    const msgDateFormatted = msg.data_formatada

    if (lastDate !== msgDateFormatted) {
      newHTML += `
          <div class="chat-date">${msgDateFormatted}</div>
      `
      lastDate = msgDateFormatted
    }

    const horario = msg.data_hora ? formatTime(msg.data_hora) : ""

    let conteudoHTML = ""

    if (msg.arquivo_url) {
      const ext = msg.arquivo_url.split(".").pop().toLowerCase()

      if (["png", "jpg", "jpeg", "gif", "webp"].includes(ext)) {
        conteudoHTML = `
            <div class="imagem-msg">
                <img src="${msg.arquivo_url}" class="img-preview">
            </div>
        `
      } else {
        const name = msg.conteudo || "Arquivo enviado"
        conteudoHTML = `
            <a href="${msg.arquivo_url}" download class="arquivo-link">
                📎 ${name}
            </a>
        `
      }
    } else {
      conteudoHTML = `<p class="texto">${escapeHtml(msg.conteudo)}</p>`
    }

    newHTML += `
        <div class="mensagens-geral ${messageClass}">
            ${conteudoHTML}
            <p class="hora">${horario}</p>
        </div>
    `
  })

  container.innerHTML = newHTML
  container.scrollTop = container.scrollHeight
}

async function sendMessage() {
  if (!currentContactId) {
    alert("Selecione um contato primeiro")
    return
  }

  if (selectedFile) {
    await sendFile()
    return
  }

  const input = document.querySelector('input[name="text"]')
  const messageText = input.value.trim()

  if (!messageText) return

  try {
    const formData = new FormData()
    formData.append("contact_id", currentContactId)
    formData.append("mensagem", messageText)

    const response = await fetch("../src/controllers/chat_backend.php?action=send_message", {
      method: "POST",
      body: formData,
    })

    const data = await response.json()

    if (data.sucesso) {
      input.value = ""
      loadMessages(currentContactId)
    } else {
      alert("Erro: " + data.erro)
    }
  } catch (error) {
    console.error("[v0] Erro ao enviar mensagem:", error)
    alert("Erro ao enviar mensagem")
  }
}

async function sendFile() {
  if (!currentContactId) {
    alert("Selecione um contato primeiro")
    return
  }

  if (!selectedFile) return

  const formData = new FormData()
  formData.append("contact_id", currentContactId)
  formData.append("file", selectedFile)

  try {
    const response = await fetch("../src/controllers/chat_backend.php?action=send_file", {
      method: "POST",
      body: formData,
    })

    const data = await response.json()

    if (data.sucesso) {
      clearFilePreview()
      loadMessages(currentContactId)
    } else {
      alert("Erro ao enviar arquivo: " + data.erro)
    }
  } catch (error) {
    console.error("Erro ao enviar arquivo:", error)
    alert("Erro ao enviar arquivo")
  }
}

function handleFileSelect(e) {
  const file = e.target.files[0]
  if (!file) return

  selectedFile = file
  showFilePreview(file)
}

function showFilePreview(file) {
  const inputContainer = document.querySelector(".input-container")

  // Remove existing preview if any
  const existingPreview = document.querySelector(".file-preview-container")
  if (existingPreview) {
    existingPreview.remove()
  }

  // Create preview container
  const previewContainer = document.createElement("div")
  previewContainer.className = "file-preview-container"

  const ext = file.name.split(".").pop().toLowerCase()
  const isImage = ["png", "jpg", "jpeg", "gif", "webp"].includes(ext)

  if (isImage) {
    // Show image preview
    const reader = new FileReader()
    reader.onload = (e) => {
      previewContainer.innerHTML = `
        <div class="file-preview-content">
          <img src="${e.target.result}" class="file-preview-image" alt="Preview">
          <div class="file-preview-info">
            <span class="file-preview-name">${file.name}</span>
            <button type="button" class="file-preview-remove" onclick="clearFilePreview()">✕</button>
          </div>
        </div>
      `
    }
    reader.readAsDataURL(file)
  } else {
    // Show file icon and name
    const fileIcon = getFileIcon(ext)
    previewContainer.innerHTML = `
      <div class="file-preview-content">
        <div class="file-preview-icon">${fileIcon}</div>
        <div class="file-preview-info">
          <span class="file-preview-name">${file.name}</span>
          <span class="file-preview-size">${formatFileSize(file.size)}</span>
          <button type="button" class="file-preview-remove" onclick="clearFilePreview()">✕</button>
        </div>
      </div>
    `
  }

  // Insert preview before the form
  inputContainer.insertBefore(previewContainer, inputContainer.firstChild)
}

function clearFilePreview() {
  selectedFile = null
  const fileInput = document.getElementById("fileInput")
  if (fileInput) {
    fileInput.value = ""
  }

  const previewContainer = document.querySelector(".file-preview-container")
  if (previewContainer) {
    previewContainer.remove()
  }
}

function getFileIcon(ext) {
  const icons = {
    pdf: "📄",
    doc: "📝",
    docx: "📝",
    zip: "🗜️",
    rar: "🗜️",
    txt: "📃",
    ppt: "📊",
    pptx: "📊",
  }
  return icons[ext] || "📎"
}

function formatFileSize(bytes) {
  if (bytes < 1024) return bytes + " B"
  if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + " KB"
  return (bytes / (1024 * 1024)).toFixed(1) + " MB"
}

function getCurrentUserId() {
  return window.CurrentUserId
}

function formatTime(timestamp) {
  const date = new Date(timestamp)
  const hours = String(date.getHours()).padStart(2, "0")
  const minutes = String(date.getMinutes()).padStart(2, "0")
  return `${hours}:${minutes}`
}

function getColorForDiscipline(disciplina) {
  if (!disciplina) return "caixa-aluno"

  const disciplinaLower = disciplina.toLowerCase()

  if (disciplinaLower.includes("matemática")) return "caixa-matematica"
  if (disciplinaLower.includes("português")) return "caixa-portugues"
  if (disciplinaLower.includes("história")) return "caixa-historia"
  if (disciplinaLower.includes("eletrônica") || disciplinaLower.includes("analógica")) return "caixa-elet-analogica"
  if (disciplinaLower.includes("biologia")) return "caixa-biologia"
  if (disciplinaLower.includes("química")) return "caixa-quimica"
  if (disciplinaLower.includes("física")) return "caixa-fisica"
  if (disciplinaLower.includes("filosofia")) return "caixa-filosofia"
  if (disciplinaLower.includes("web")) return "caixa-WEB"
  if (disciplinaLower.includes("geografia")) return "caixa-geografia"

  return "caixa-aluno"
}

function extractDisciplineName(disciplina) {
  if (!disciplina) return "Aluno"
  return disciplina.split("-")[0].trim()
}

function escapeHtml(text) {
  const map = {
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    '"': "&quot;",
    "'": "&#039;",
  }
  return text.replace(/[&<>"']/g, (m) => map[m])
}

document.getElementById("searchInput").addEventListener("input", async function () {
  const termo = this.value.trim()

  const url =
    termo.length > 0
      ? `../src/controllers/chat_backend.php?action=search_contacts&q=${encodeURIComponent(termo)}`
      : `../src/controllers/chat_backend.php?action=get_contacts`

  try {
    const response = await fetch(url)
    const data = await response.json()

    if (data.sucesso) {
      renderContacts(data.contatos)
    }
  } catch (error) {
    console.error("Erro ao pesquisar contatos:", error)
  }
})
