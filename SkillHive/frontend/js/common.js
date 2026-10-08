/* SkillHive common.js - complete file */
const API_BASE = "https://skillhive.kesug.com/backend";

/* ---------- API helper ---------- */ 
async function api(path, method, body) {
  method = method || "GET";
  const options = {
    method: method,
    credentials: "include",
    headers: { "Content-Type": "application/json" }
  };
  if (body && method !== "GET") {
    options.body = JSON.stringify(body);
  }
  try {
    if (path.charAt(0) !== "/") { path = "/" + path; }
    const res = await fetch(API_BASE + path, options);
    const text = await res.text();
    try {
      return JSON.parse(text);
    } catch (e) {
      return { success: false, data: null, message: "Server error: " + text.slice(0, 120) };
    }
  } catch (err) {
    return { success: false, data: null, message: "Cannot reach server. Is XAMPP running?" };
  }
}

/* ---------- Toast ---------- */
function showToast(message, type) {
  type = type || "info";
  let box = document.getElementById("toast-container");
  if (!box) {
    box = document.createElement("div");
    box.id = "toast-container";
    document.body.appendChild(box);
  }
  const t = document.createElement("div");
  t.className = "toast toast-" + type;
  t.textContent = message;
  t.style.cssText =
    "padding:12px 18px;margin-top:8px;border-radius:10px;color:#fff;font-weight:500;" +
    "box-shadow:0 4px 14px rgba(0,0,0,.2);background:" +
    (type === "error" ? "#dc2626" : type === "success" ? "#16a34a" : "#4f46e5");
  box.appendChild(t);
  setTimeout(function () { t.remove(); }, 3500);
}

/* ---------- Theme ---------- */
function applyTheme(theme) {
  document.documentElement.setAttribute("data-theme", theme);
  document.documentElement.setAttribute("data-bs-theme", theme);
  if (document.body) {
    document.body.classList.toggle("dark", theme === "dark");
    document.body.classList.toggle("dark-mode", theme === "dark");
  }
}

function initTheme() {
  let theme = "light";
  try { theme = localStorage.getItem("theme") || "light"; } catch (e) {}
  applyTheme(theme);
  const btn = document.querySelector(".theme-toggle");
  if (btn && !btn.getAttribute("onclick") && !btn.dataset.wired) {
    btn.dataset.wired = "1";
    btn.addEventListener("click", toggleTheme);
  }
}

function toggleTheme() {
  const current = document.documentElement.getAttribute("data-theme") || "light";
  const next = current === "dark" ? "light" : "dark";
  try { localStorage.setItem("theme", next); } catch (e) {}
  applyTheme(next);
}

/* ---------- Login helpers ---------- */
async function requireLogin() {
  const r = await api("/auth/me.php");
  if (!r.success) {
    window.location.href = "login.html";
    return null;
  }
  const user = (r.data && r.data.user) || r.data;
  if (user && String(user.onboarding_complete) === "0" &&
      !window.location.pathname.endsWith("onboarding.html") &&
      user.role !== "admin") {
    window.location.href = "onboarding.html";
    return null;
  }
  return user;
}

async function logout() {
  await api("/auth/logout.php", "POST");
  window.location.href = "login.html";
}

/* ---------- Password helpers ---------- */
function checkPasswordStrength(pw) {
  pw = pw || "";
  let score = 0;
  if (pw.length >= 8) score++;
  if (/[a-z]/.test(pw) && /[A-Z]/.test(pw)) score++;
  if (/\d/.test(pw)) score++;
  if (/[^A-Za-z0-9]/.test(pw)) score++;
  const labels = ["Too weak", "Weak", "Fair", "Good", "Strong"];
  const colors = ["#dc2626", "#dc2626", "#f59e0b", "#16a34a", "#16a34a"];
  return {
    score: score,
    label: labels[score],
    text: labels[score],
    color: colors[score],
    percent: score * 25
  };
}

function wirePasswordToggle(inputOrId, buttonOrId) {
  const input = typeof inputOrId === "string" ? document.getElementById(inputOrId) : inputOrId;
  const btn = typeof buttonOrId === "string" ? document.getElementById(buttonOrId) : buttonOrId;
  if (!input || !btn) return;
  btn.addEventListener("click", function () {
    const show = input.type === "password";
    input.type = show ? "text" : "password";
    btn.textContent = show ? "Hide" : "Show";
  });
}

/* =====================================================
   SkillHive UI upgrade (sidebar, logo, avatar, banner)
   Runs after the page loads. Wrapped so it can never
   break the rest of the page.
   ===================================================== */
document.addEventListener("DOMContentLoaded", function () {
  try {
    const nav = document.querySelector(".navbar");
    if (!nav) return;

    document.body.classList.add("sh-side");

    const icons = {
      "dashboard.html": "🏠",
      "matches.html": "🤝",
      "my-skills.html": "🎯",
      "myskills.html": "🎯",
      "skills.html": "🎯",
      "connections.html": "🔗",
      "sessions.html": "📅",
      "reviews.html": "⭐",
      "profile.html": "👤",
      "admin.html": "🛠️",
      "admin-activity.html": "📊"
    };

    /* icons on links */
    const links = nav.querySelectorAll(".navbar-links a");
    links.forEach(function (a) {
      if (a.dataset.shIcon) return;
      a.dataset.shIcon = "1";
      const file = (a.getAttribute("href") || "").split("/").pop().split("?")[0];
      const icon = icons[file] || "•";
      a.innerHTML = '<span class="sh-icon">' + icon + "</span> " + a.innerHTML;
    });

    /* logo */
    const brand = nav.querySelector(".navbar-brand");
    if (brand && !brand.dataset.shLogo) {
      brand.dataset.shLogo = "1";
      brand.innerHTML = '<span class="sh-logo">🐝</span> ' + brand.innerHTML;
    }

    /* bottom of sidebar: Logout + small round theme button side by side */
    if (!nav.querySelector(".sh-bottom")) {
      const themeBtn = nav.querySelector(".theme-toggle");
      let logoutBtn = nav.querySelector('[onclick*="logout"], #logout, #logoutBtn, #logout-btn');
      if (!logoutBtn) {
        const cands = nav.querySelectorAll(".btn-outline, button.btn, a.btn");
        cands.forEach(function (c) { if (!logoutBtn && c !== themeBtn) logoutBtn = c; });
      }
      if (themeBtn || logoutBtn) {
        const bottom = document.createElement("div");
        bottom.className = "sh-bottom";
        if (logoutBtn) bottom.appendChild(logoutBtn);
        if (themeBtn) bottom.appendChild(themeBtn);
        nav.appendChild(bottom);
      }
    }

    /* user box + banner + admin link */
    api("/auth/me.php").then(function (r) {
      if (!r || !r.success) return;
      const user = (r.data && r.data.user) || r.data || {};
      const name = user.name || "User";
      const linksBox = nav.querySelector(".navbar-links");

      /* admin menu: only Admin + Profile */
      if (user.role === "admin" && linksBox) {
        const studentPages = ["dashboard.html", "matches.html", "my-skills.html", "myskills.html",
                              "skills.html", "connections.html", "sessions.html", "reviews.html"];
        const here = window.location.pathname.split("/").pop();
        if (studentPages.indexOf(here) !== -1) {
          window.location.href = "admin.html";
          return;
        }
        linksBox.querySelectorAll("a").forEach(function (a) {
          const file = (a.getAttribute("href") || "").split("/").pop().split("?")[0];
          if (studentPages.indexOf(file) !== -1) { a.remove(); }
        });
        if (!linksBox.querySelector('a[href$="admin.html"]')) {
          const a = document.createElement("a");
          a.href = "admin.html";
          a.innerHTML = '<span class="sh-icon">🛠️</span> Admin';
          linksBox.insertBefore(a, linksBox.firstChild);
        }
        if (!linksBox.querySelector('a[href$="admin-activity.html"]')) {
          const act = document.createElement("a");
          act.href = "admin-activity.html";
          act.innerHTML = '<span class="sh-icon">📊</span> Activity';
          const adminLink = linksBox.querySelector('a[href$="admin.html"]');
          linksBox.insertBefore(act, adminLink ? adminLink.nextSibling : linksBox.firstChild);
        }
        const cur = linksBox.querySelector('a[href$="' + here + '"]');
        if (cur) { cur.classList.add("active"); }
      }

      /* avatar */
      let photo = null;
      try { photo = localStorage.getItem("sh_photo_" + user.id); } catch (e) {}

      const box = document.createElement("div");
      box.className = "sh-userbox";
      box.innerHTML =
        '<div class="sh-avatar" id="sh-avatar">' +
        (photo ? '<img src="' + photo + '" alt="">' : name.charAt(0).toUpperCase()) +
        "</div>" +
        '<div class="sh-username">' + name.replace(/</g, "&lt;") + "</div>";
      const brandEl = nav.querySelector(".navbar-brand");
      if (brandEl) {
        brandEl.insertAdjacentElement("afterend", box);
      } else {
        nav.insertBefore(box, nav.firstChild);
      }

      /* banner */
      const h1 = document.querySelector("h1");
      if (h1 && !document.querySelector(".sh-banner")) {
        const banner = document.createElement("div");
        banner.className = "sh-banner";
        banner.innerHTML = "<h2>Hi, " + name.replace(/</g, "&lt;") + " 👋</h2><p>Learn a skill. Teach a skill.</p>";
        const holder = h1.parentNode;
        holder.insertBefore(banner, holder.firstChild);
      }

      /* profile photo upload (saved in this browser only) */
      if (window.location.pathname.endsWith("profile.html")) {
        const input = document.createElement("input");
        input.type = "file";
        input.accept = "image/*";
        input.style.display = "none";
        document.body.appendChild(input);
        const av = document.getElementById("sh-avatar");
        av.style.cursor = "pointer";
        av.title = "Click to change photo";
        av.addEventListener("click", function () { input.click(); });
        input.addEventListener("change", function () {
          const file = input.files[0];
          if (!file) return;
          const reader = new FileReader();
          reader.onload = function () {
            try { localStorage.setItem("sh_photo_" + user.id, reader.result); } catch (e) {
              showToast("Photo too big. Try a smaller one.", "error");
              return;
            }
            av.innerHTML = '<img src="' + reader.result + '" alt="">';
            showToast("Photo updated", "success");
          };
          reader.readAsDataURL(file);
        });
      }
    });
  } catch (err) {
    console.error("UI upgrade skipped:", err);
  }
});