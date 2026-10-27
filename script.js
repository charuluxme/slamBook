function escapeText(value) {
  return (value || "-").toString().trim();
}

function normalizeRole(value) {
  return (value || "").toString().trim().toLowerCase();
}

function parseDateSafe(value) {
  const date = new Date(value);
  return Number.isNaN(date.getTime()) ? null : date;
}

function getCsrfToken() {
  const tokenMeta = document.querySelector('meta[name="csrfToken"]');
  return tokenMeta ? tokenMeta.getAttribute("content") || "" : "";
}

function initDashboardSidebar() {
  const dashboardShell = document.querySelector(".dashboard-shell");
  const dashboardSidebar = document.querySelector(".dashboard-sidebar");
  const sidebarToggleBtn = document.getElementById("sidebarToggleBtn");
  const sidebarBackdrop = document.getElementById("sidebarBackdrop");

  if (!dashboardShell || !dashboardSidebar || !sidebarToggleBtn || !sidebarBackdrop) {
    return;
  }

  const mobileMediaQuery = window.matchMedia("(max-width: 760px)");

  function setSidebarOpen(isOpen) {
    dashboardShell.classList.toggle("sidebarOpen", isOpen);
    document.body.classList.toggle("sidebarOpen", isOpen);
    sidebarToggleBtn.setAttribute("aria-expanded", String(isOpen));
  }

  function closeSidebarIfMobile() {
    if (!mobileMediaQuery.matches) {
      return;
    }
    setSidebarOpen(false);
  }

  sidebarToggleBtn.addEventListener("click", () => {
    const shouldOpen = !dashboardShell.classList.contains("sidebarOpen");
    setSidebarOpen(shouldOpen);
  });

  sidebarBackdrop.addEventListener("click", () => {
    setSidebarOpen(false);
  });

  dashboardSidebar.querySelectorAll(".sidebar-link").forEach((link) => {
    link.addEventListener("click", closeSidebarIfMobile);
  });

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape") {
      setSidebarOpen(false);
    }
  });

  mobileMediaQuery.addEventListener("change", (event) => {
    if (!event.matches) {
      setSidebarOpen(false);
    }
  });
}

function initWriterPage() {
  const form = document.getElementById("slamForm");
  const feedback = document.getElementById("formFeedback");

  if (!form || !feedback) {
    return;
  }

  form.addEventListener("submit", (event) => {
    event.preventDefault();
    if (!form.checkValidity()) {
      form.reportValidity();
      return;
    }

    const formData = new FormData(form);
    formData.append("action", "submit_memory");
    formData.append("csrfToken", getCsrfToken());

    feedback.textContent = "Saving memory...";

    $.ajax({
      url: "slamBackend.php",
      type: "POST",
      data: formData,
      processData: false,
      contentType: false,
      dataType: "json",
      success: (response) => {
        if (!response.success) {
          feedback.textContent = response.message || "Failed to save memory.";
          return;
        }

        form.reset();
        feedback.textContent = response.message || "Memory saved successfully.";
        window.setTimeout(() => {
          feedback.textContent = "";
        }, 3200);
      },
      error: (xhr) => {
        const fallback = "Unable to save memory right now. Please try again.";
        const serverMessage = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : fallback;
        feedback.textContent = serverMessage;
      },
    });
  });
}

function initAdminLoginPage() {
  const loginForm = document.getElementById("adminLoginForm");
  const loginFeedback = document.getElementById("loginFeedback");

  if (!loginForm || !loginFeedback) {
    return;
  }

  loginForm.addEventListener("submit", (event) => {
    event.preventDefault();

    if (!loginForm.checkValidity()) {
      loginForm.reportValidity();
      return;
    }

    const formData = new FormData(loginForm);
    const userName = (formData.get("username") || "").toString().trim();
    const password = (formData.get("password") || "").toString();

    loginFeedback.textContent = "Signing in...";

    $.ajax({
      url: "slamBackend.php",
      type: "POST",
      dataType: "json",
      data: {
        action: "admin_login",
        userName,
        password,
        csrfToken: getCsrfToken(),
      },
      success: (response) => {
        if (!response.success) {
          loginFeedback.textContent = response.message || "Invalid username or password.";
          return;
        }

        const redirectUrl = response.data && response.data.redirectUrl ? response.data.redirectUrl : "adminDashboard.php";
        window.location.href = redirectUrl;
      },
      error: (xhr) => {
        const fallback = "Unable to login right now. Please try again.";
        const serverMessage = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : fallback;
        loginFeedback.textContent = serverMessage;
      },
    });
  });
}

function initAdminDashboardPage() {
  const entriesList = document.getElementById("entriesList");
  const entryTemplate = document.getElementById("entryTemplate");
  const statsBar = document.getElementById("statsBar");
  const searchInput = document.getElementById("searchInput");
  const filterRole = document.getElementById("filterRole");
  const logoutBtn = document.getElementById("logoutBtn");
  const userMenuBtn = document.getElementById("userMenuBtn");
  const userDropdown = document.getElementById("userDropdown");
  const writerModal = document.getElementById("writerModal");
  const modalCloseBtn = document.getElementById("modalCloseBtn");
  const modalWriterImage = document.getElementById("modalWriterImage");
  const modalWriterName = document.getElementById("modalWriterName");
  const modalWriterSub = document.getElementById("modalWriterSub");
  const modalCreatedAt = document.getElementById("modalCreatedAt");
  const modalDateOfBirth = document.getElementById("modalDateOfBirth");
  const modalNickname = document.getElementById("modalNickname");
  const modalImpression = document.getElementById("modalImpression");
  const modalMemory = document.getElementById("modalMemory");
  const modalLearning = document.getElementById("modalLearning");
  const modalFutureMessage = document.getElementById("modalFutureMessage");
  const modalBatch = document.getElementById("modalBatch");
  const modalContact = document.getElementById("modalContact");
  const welcomeAdminName = document.getElementById("welcomeAdminName");
  const welcomeTotalCount = document.getElementById("welcomeTotalCount");
  const countTotal = document.getElementById("countTotal");
  const countFriend = document.getElementById("countFriend");
  const countJunior = document.getElementById("countJunior");
  const countClassmate = document.getElementById("countClassmate");
  const countColleague = document.getElementById("countColleague");
  const countFaculty = document.getElementById("countFaculty");
  const countThisMonth = document.getElementById("countThisMonth");
  const roleDistributionChart = document.getElementById("roleDistributionChart");
  const monthlyTrendChart = document.getElementById("monthlyTrendChart");
  const paginationPrev = document.getElementById("paginationPrev");
  const paginationNext = document.getElementById("paginationNext");
  const paginationInfo = document.getElementById("paginationInfo");

  const isAdminPage = !!(logoutBtn || welcomeAdminName || statsBar || entriesList || roleDistributionChart || monthlyTrendChart);
  if (!isAdminPage) {
    return;
  }

  let roleDistributionChartInstance = null;
  let monthlyTrendChartInstance = null;
  let currentPage = 1;
  const pageSize = 12;

  if (welcomeAdminName) {
    const adminNameNode = document.querySelector(".user-meta strong");
    const adminName = adminNameNode ? adminNameNode.textContent.trim() : "Admin";
    welcomeAdminName.textContent = `Hello, ${adminName}`;
  }

  function closeWriterModal() {
    if (!writerModal) {
      return;
    }
    writerModal.classList.add("hidden");
    writerModal.setAttribute("aria-hidden", "true");
    document.body.classList.remove("modalOpen");
  }

  function openWriterModal(entry) {
    if (
      !writerModal ||
      !modalWriterImage ||
      !modalWriterName ||
      !modalWriterSub ||
      !modalCreatedAt ||
      !modalDateOfBirth ||
      !modalNickname ||
      !modalImpression ||
      !modalMemory ||
      !modalLearning ||
      !modalFutureMessage ||
      !modalBatch ||
      !modalContact
    ) {
      return;
    }

    modalWriterName.textContent = escapeText(entry.name);
    modalWriterSub.textContent = `${escapeText(entry.relation)} | Batch ${escapeText(entry.batch)}`;
    modalDateOfBirth.textContent = escapeText(entry.dateOfBirth);
    modalNickname.textContent = escapeText(entry.nickname);
    modalImpression.textContent = escapeText(entry.impression);
    modalMemory.textContent = escapeText(entry.memory);
    modalLearning.textContent = escapeText(entry.learning);
    modalFutureMessage.textContent = escapeText(entry.futureMessage);
    modalBatch.textContent = escapeText(entry.batch);
    modalContact.textContent = escapeText(entry.contact);

    const createdAt = new Date(entry.createdAt);
    modalCreatedAt.textContent = Number.isNaN(createdAt.getTime()) ? "Saved recently" : createdAt.toLocaleString();

    if (entry.photoPath) {
      modalWriterImage.src = entry.photoPath;
    } else {
      modalWriterImage.src = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='120' height='120'%3E%3Crect width='100%25' height='100%25' fill='%23e9eef3'/%3E%3Ctext x='50%25' y='53%25' dominant-baseline='middle' text-anchor='middle' font-family='Arial' font-size='34' fill='%23627a92'%3E%3F%3C/text%3E%3C/svg%3E";
    }

    writerModal.classList.remove("hidden");
    writerModal.setAttribute("aria-hidden", "false");
    document.body.classList.add("modalOpen");
    if (modalCloseBtn) {
      modalCloseBtn.focus();
    }
  }

  if (userMenuBtn && userDropdown) {
    userMenuBtn.addEventListener("click", (event) => {
      event.stopPropagation();
      const isHidden = userDropdown.classList.contains("hidden");
      userDropdown.classList.toggle("hidden", !isHidden);
      userMenuBtn.setAttribute("aria-expanded", String(isHidden));
    });

    document.addEventListener("click", (event) => {
      if (!userDropdown.classList.contains("hidden") && !event.target.closest(".user-menu-wrap")) {
        userDropdown.classList.add("hidden");
        userMenuBtn.setAttribute("aria-expanded", "false");
      }
    });
  }

  function buildStatPillsFromStats(stats) {
    if (!statsBar) {
      return;
    }

    const total = Number(stats && stats.total ? stats.total : 0);
    const roleCounts = (stats && stats.roleCounts) || {};

    const fragments = [`<span class="stat-pill">Total: ${total}</span>`];
    Object.keys(roleCounts)
      .sort()
      .forEach((role) => {
        fragments.push(`<span class="stat-pill">${role}: ${Number(roleCounts[role] || 0)}</span>`);
      });

    statsBar.innerHTML = fragments.join("");
  }

  function renderChartsFromStats(stats) {
    if (!roleDistributionChart && !monthlyTrendChart) {
      return;
    }

    if (typeof Chart === "undefined") {
      if (statsBar) {
        statsBar.innerHTML = '<span class="stat-pill">Chart library could not load.</span>';
      }
      return;
    }

    const roleCounts = (stats && stats.roleCounts) || {};
    const friendCount = Number(roleCounts.Friend || 0);
    const juniorCount = Number(roleCounts.Junior || 0);
    const classmateCount = Number(roleCounts.Classmate || 0);
    const colleagueCount = Number(roleCounts.Colleague || 0);
    const facultyCount = Number(roleCounts.Faculty || 0);

    if (roleDistributionChart) {
      if (roleDistributionChartInstance) {
        roleDistributionChartInstance.destroy();
      }

      roleDistributionChartInstance = new Chart(roleDistributionChart, {
        type: "doughnut",
        data: {
          labels: ["Friend", "Junior", "Classmate", "Colleague", "Faculty"],
          datasets: [
            {
              data: [friendCount, juniorCount, classmateCount, colleagueCount, facultyCount],
              backgroundColor: ["#1f6f8b", "#4f8aa3", "#74a8bd", "#9abfd0", "#c2d9e6"],
              borderWidth: 1,
            },
          ],
        },
        options: {
          plugins: {
            legend: {
              position: "bottom",
            },
          },
          maintainAspectRatio: false,
        },
      });
    }

    if (monthlyTrendChart) {
      if (monthlyTrendChartInstance) {
        monthlyTrendChartInstance.destroy();
      }

      const monthlyTrend = (stats && stats.monthlyTrend) || { labels: [], counts: [] };

      monthlyTrendChartInstance = new Chart(monthlyTrendChart, {
        type: "bar",
        data: {
          labels: monthlyTrend.labels,
          datasets: [
            {
              label: "Memories",
              data: monthlyTrend.counts,
              backgroundColor: "#1f6f8b",
              borderRadius: 8,
            },
          ],
        },
        options: {
          plugins: {
            legend: {
              display: false,
            },
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                precision: 0,
              },
            },
          },
          maintainAspectRatio: false,
        },
      });
    }
  }

  function updateDashboardCountersFromStats(stats) {
    const total = Number(stats && stats.total ? stats.total : 0);
    const roleCounts = (stats && stats.roleCounts) || {};
    const friend = Number(roleCounts.Friend || 0);
    const junior = Number(roleCounts.Junior || 0);
    const classmate = Number(roleCounts.Classmate || 0);
    const colleague = Number(roleCounts.Colleague || 0);
    const faculty = Number(roleCounts.Faculty || 0);
    const thisMonth = Number(stats && stats.thisMonth ? stats.thisMonth : 0);

    if (welcomeTotalCount) welcomeTotalCount.textContent = String(total);
    if (countTotal) countTotal.textContent = String(total);
    if (countFriend) countFriend.textContent = String(friend);
    if (countJunior) countJunior.textContent = String(junior);
    if (countClassmate) countClassmate.textContent = String(classmate);
    if (countColleague) countColleague.textContent = String(colleague);
    if (countFaculty) countFaculty.textContent = String(faculty);
    if (countThisMonth) countThisMonth.textContent = String(thisMonth);
  }

  function renderEmptyState(message) {
    if (!entriesList) {
      return;
    }

    entriesList.innerHTML = `<div class="empty">${message}</div>`;
  }

  function renderEntries(entries) {
    const safeEntries = Array.isArray(entries) ? entries : [];

    if (!entriesList || !entryTemplate) {
      return;
    }

    if (!safeEntries.length) {
      renderEmptyState("No memories yet. Ask writers to submit one.");
      return;
    }

    entriesList.innerHTML = "";

    safeEntries.forEach((entry) => {
      const node = entryTemplate.content.cloneNode(true);
      const cardEl = node.querySelector(".writer-card");
      node.querySelector(".writer-card-name").textContent = escapeText(entry.name);
      node.querySelector(".writer-card-sub").textContent = `${escapeText(entry.relation)} | Batch ${escapeText(entry.batch)}`;

      const photoEl = node.querySelector(".writer-card-image");
      if (entry.photoPath) {
        photoEl.src = entry.photoPath;
      } else {
        photoEl.src = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='84' height='84'%3E%3Crect width='100%25' height='100%25' fill='%23e9eef3'/%3E%3Ctext x='50%25' y='53%25' dominant-baseline='middle' text-anchor='middle' font-family='Arial' font-size='26' fill='%23627a92'%3E%3F%3C/text%3E%3C/svg%3E";
      }

      const createdAt = new Date(entry.createdAt);
      node.querySelector(".entry-time").textContent = Number.isNaN(createdAt.getTime())
        ? "Saved recently"
        : createdAt.toLocaleString();

      cardEl.addEventListener("click", (event) => {
        event.preventDefault();
        openWriterModal(entry);
      });

      cardEl.addEventListener("keydown", (event) => {
        if (event.key === "Enter" || event.key === " ") {
          event.preventDefault();
          openWriterModal(entry);
        }
      });

      entriesList.appendChild(node);
    });
  }

  function updatePagination(pagination) {
    if (!paginationPrev || !paginationNext || !paginationInfo) {
      return;
    }

    const page = Number(pagination && pagination.page ? pagination.page : 1);
    const totalPages = Number(pagination && pagination.totalPages ? pagination.totalPages : 1);
    paginationInfo.textContent = `Page ${page} of ${totalPages}`;
    paginationPrev.disabled = !(pagination && pagination.hasPrev);
    paginationNext.disabled = !(pagination && pagination.hasNext);
  }

  function fetchMemories() {
    const searchValue = searchInput ? searchInput.value.trim() : "";
    const filterRoleValue = filterRole ? filterRole.value : "All";
    const activePage = entriesList ? currentPage : 1;

    $.ajax({
      url: "slamBackend.php",
      type: "GET",
      dataType: "json",
      data: {
        action: "fetch_memories",
        searchValue,
        filterRole: filterRoleValue,
        page: activePage,
        pageSize,
      },
      success: (response) => {
        if (!response.success) {
          renderEmptyState(response.message || "Unable to load memories.");
          return;
        }

        const responseData = response.data || {};
        const memories = Array.isArray(responseData.memories) ? responseData.memories : [];
        const stats = responseData.stats || { total: 0, roleCounts: {}, thisMonth: 0, monthlyTrend: { labels: [], counts: [] } };
        const pagination = responseData.pagination || null;

        if (pagination && pagination.page) {
          currentPage = Number(pagination.page) || 1;
        }

        buildStatPillsFromStats(stats);
        updateDashboardCountersFromStats(stats);
        renderChartsFromStats(stats);
        renderEntries(memories);
        updatePagination(pagination);
      },
      error: (xhr) => {
        if (xhr.status === 401) {
          window.location.href = "adminLogin.php";
          return;
        }
        renderEmptyState("Unable to load memories right now.");
      },
    });
  }

  if (searchInput) {
    searchInput.addEventListener("input", () => {
      currentPage = 1;
      fetchMemories();
    });
  }

  if (filterRole) {
    filterRole.addEventListener("change", () => {
      currentPage = 1;
      fetchMemories();
    });
  }

  if (paginationPrev) {
    paginationPrev.addEventListener("click", () => {
      if (currentPage <= 1) {
        return;
      }
      currentPage -= 1;
      fetchMemories();
    });
  }

  if (paginationNext) {
    paginationNext.addEventListener("click", () => {
      currentPage += 1;
      fetchMemories();
    });
  }

  if (modalCloseBtn) {
    modalCloseBtn.addEventListener("click", closeWriterModal);
  }

  if (writerModal) {
    writerModal.addEventListener("click", (event) => {
      if (event.target === writerModal) {
        closeWriterModal();
      }
    });
  }

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape") {
      closeWriterModal();
    }
  });

  if (logoutBtn) {
    logoutBtn.addEventListener("click", () => {
      $.ajax({
        url: "slamBackend.php",
        type: "POST",
        dataType: "json",
        data: {
          action: "admin_logout",
          csrfToken: getCsrfToken(),
        },
        complete: () => {
          window.location.href = "adminLogin.php";
        },
      });
    });
  }

  fetchMemories();
}

document.addEventListener("DOMContentLoaded", () => {
  initDashboardSidebar();
  initWriterPage();
  initAdminLoginPage();
  initAdminDashboardPage();
});
