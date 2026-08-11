/**
 * JS global — carregado em todas as páginas.
 * Registra o Service Worker e expõe utilitários compartilhados.
 */

// --- Service Worker (PWA) ---
// As páginas ficam em frontend/, mas o sw.js fica na raiz do app para
// que o escopo cubra tudo (incluindo o index.php que redireciona).
if ("serviceWorker" in navigator) {
  window.addEventListener("load", () => {
    navigator.serviceWorker
      .register("../sw.js", { scope: "../" })
      .then(() => console.log("ServiceWorker registrado com sucesso!"))
      .catch((error) => console.log("Erro ao registrar ServiceWorker:", error));
  });
}

/**
 * Mostra uma mensagem na caixa #alert-box da página.
 * @param {string} message
 * @param {boolean} success
 */
function showAlert(message, success) {
  const alertBox = document.getElementById("alert-box");
  if (!alertBox) return;

  alertBox.textContent = message;
  alertBox.className = `alert ${success ? "alert-success" : "alert-error"}`;
  alertBox.style.display = "block";
}

/**
 * POST em JSON, devolvendo a resposta já convertida.
 * @param {string} url
 * @param {object} data
 * @returns {Promise<object>}
 */
async function postJson(url, data) {
  const response = await fetch(url, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(data),
  });
  return response.json();
}
