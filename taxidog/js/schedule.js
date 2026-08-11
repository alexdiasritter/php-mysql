/** Envio do formulário de agendamento (schedule.php) */

const scheduleForm = document.getElementById("schedule-form");

if (scheduleForm) {
  scheduleForm.addEventListener("submit", async (event) => {
    event.preventDefault();

    const ride = {
      client: document.getElementById("client").value,
      origin: document.getElementById("origin").value,
      destination: document.getElementById("destination").value,
      price: document.getElementById("price").value,
      period: document.getElementById("period").value,
      animal_quantity: document.getElementById("animal_quantity").value,
      ride_date: document.getElementById("ride_date").value,
    };

    try {
      const result = await postJson("schedule.php", ride);
      showAlert(result.message, result.success);

      if (result.success) {
        scheduleForm.reset();
        setTimeout(() => location.reload(), 10);
      }
    } catch (error) {
      console.error("Erro ao agendar:", error);
      showAlert("Erro de conexão. Tente novamente.", false);
    }
  });
}
