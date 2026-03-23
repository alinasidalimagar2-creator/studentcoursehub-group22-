document.addEventListener("DOMContentLoaded", () => {
  const programmeContainer = document.getElementById("programmeContainer");
  const levelFilter = document.getElementById("levelFilter");
  const searchBar = document.getElementById("searchBar");

  let programmes = [];

  // ===== Fetch and Display Programmes =====
  function fetchProgrammes() {
    fetch("backend/fetch_programmes.php")
      .then((res) => res.json())
      .then((data) => {
        programmes = data;
        // Derive Level based on ProgrammeName
        programmes = programmes.map((p) => ({
          ...p,
          Level: p.ProgrammeName.startsWith("BSc")
            ? "Undergraduate"
            : p.ProgrammeName.startsWith("MSc")
            ? "Postgraduate"
            : "Other",
        }));
        renderProgrammes(programmes);
      })
      .catch((error) => {
        console.error("Error:", error);
        programmeContainer.innerHTML =
          "<p style='color:red;'>Unable to load programmes. Please try again later.</p>";
      });
  }

  // ===== Render Programmes =====
  function renderProgrammes(list) {
    programmeContainer.innerHTML = "";

    if (list.length === 0) {
      programmeContainer.innerHTML =
        "<p style='color:#ccc;'>No programmes found matching your filters.</p>";
      return;
    }

    list.forEach((p) => {
      const card = document.createElement("div");
      card.classList.add("programme-card");
      card.innerHTML = `
        <h3>${p.ProgrammeName}</h3>
        <p>${p.Description}</p>
        <p><strong>Level:</strong> ${p.Level}</p>
        <button class="register-btn" data-id="${p.ProgrammeID}">
          Register Interest
        </button>
      `;
      programmeContainer.appendChild(card);
    });

    attachRegisterButtons();
  }

  // ===== Filter Programmes =====
  function filterProgrammes() {
    const selectedLevel = levelFilter.value.toLowerCase();
    const keyword = searchBar.value.toLowerCase();

    const filtered = programmes.filter((p) => {
      const levelMatch =
        selectedLevel === "all" ||
        p.Level.toLowerCase() === selectedLevel;
      const keywordMatch =
        p.ProgrammeName.toLowerCase().includes(keyword) ||
        p.Description.toLowerCase().includes(keyword);
      return levelMatch && keywordMatch;
    });

    renderProgrammes(filtered);
  }

  // ===== Attach Filter Events =====
  levelFilter.addEventListener("change", filterProgrammes);
  searchBar.addEventListener("input", filterProgrammes);

  // ===== Register Interest Buttons =====
  function attachRegisterButtons() {
    const buttons = document.querySelectorAll(".register-btn");
    buttons.forEach((btn) => {
      btn.addEventListener("click", () => {
        const programmeID = btn.getAttribute("data-id");
        window.location.href =
          "backend/register_interest.php?programmeID=" + programmeID;
      });
    });
  }
  // ===== Initialize =====
  fetchProgrammes();
  
});
