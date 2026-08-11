/** Envio do formulário de login (index.php) */

const loginForm = document.getElementById("login-form");

if (loginForm) {
  loginForm.addEventListener("submit", async (event) => {
    event.preventDefault();

    const credentials = {
      username: document.getElementById("username").value,
      password: document.getElementById("password").value,
    };

    try {
      const result = await postJson("index.php", credentials);
      showAlert(result.message, result.success);

      if (result.success) {
        window.location.href = "home.php";
      }
    } catch (error) {
      console.error("Erro ao fazer login:", error);
      showAlert("Erro de conexão. Tente novamente.", false);
    }
  });
}
