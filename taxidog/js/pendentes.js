/** Modal de edição de corrida (pendentes.php) */

const editModal = document.getElementById("editModal");

/** Converte "2026-03-07 14:30:00" para o formato do input datetime-local */
function toDatetimeLocal(value) {
  const date = new Date(value.replace(" ", "T"));
  const pad = (n) => String(n).padStart(2, "0");

  return (
    date.getFullYear() +
    "-" +
    pad(date.getMonth() + 1) +
    "-" +
    pad(date.getDate()) +
    "T" +
    pad(date.getHours()) +
    ":" +
    pad(date.getMinutes())
  );
}

function fillModal(ride) {
  document.getElementById("edit_id").value = ride.id;
  document.getElementById("edit_ride_date").value = toDatetimeLocal(ride.ride_date);
  document.getElementById("edit_client").value = ride.client || "";
  document.getElementById("edit_origin").value = ride.origin;
  document.getElementById("edit_destination").value = ride.destination;
  document.getElementById("edit_animal_quantity").value = ride.animal_quantity;
  document.getElementById("edit_period").value = ride.period;
  document.getElementById("edit_price").value = ride.price;
}

function openModal() {
  editModal.classList.add("active");
  document.body.style.overflow = "hidden";
}

function closeModal() {
  editModal.classList.remove("active");
  document.body.style.overflow = "";
}

document.querySelectorAll(".btn-edit").forEach((button) => {
  button.addEventListener("click", async () => {
    try {
      const response = await fetch("get_ride.php?id=" + button.dataset.id);
      const ride = await response.json();

      if (ride.error) {
        showAlert(ride.error, false);
        return;
      }

      fillModal(ride);
      openModal();
    } catch (error) {
      console.error("Erro ao carregar dados:", error);
      showAlert("Não foi possível carregar os dados da corrida.", false);
    }
  });
});

if (editModal) {
  document.getElementById("closeModalBtn").addEventListener("click", closeModal);

  // Clique fora da caixa fecha o modal
  editModal.addEventListener("click", (event) => {
    if (event.target === editModal) closeModal();
  });
}
