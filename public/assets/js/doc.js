"use strict";

// ── Progress bar ──────────────────────────────────────────────────────────────
const progress = document.getElementById("progress");
window.addEventListener(
  "scroll",
  () => {
    const pct =
      window.scrollY / (document.body.scrollHeight - window.innerHeight);
    progress.style.transform = `scaleX(${Math.min(pct, 1)})`;
  },
  { passive: true },
);

// ── Sidebar navigation (highlight active) ────────────────────────────────────
const sections = document.querySelectorAll(".doc-section");
const links = document.querySelectorAll(".sidebar-link");

function nav(id) {
  const el = document.getElementById(id);
  if (el) {
    el.scrollIntoView({ behavior: "smooth", block: "start" });
  }
  links.forEach((l) => l.classList.remove("active"));
  const target = [...links].find((l) =>
    l.getAttribute("onclick")?.includes(`'${id}'`),
  );
  if (target) target.classList.add("active");
}

// Intersection observer to highlight sidebar on scroll
const io = new IntersectionObserver(
  (entries) => {
    entries.forEach((e) => {
      if (e.isIntersecting) {
        const id = e.target.id;
        links.forEach((l) => {
          l.classList.toggle(
            "active",
            l.getAttribute("onclick")?.includes(`'${id}'`),
          );
        });
      }
    });
  },
  { rootMargin: "-10% 0px -80% 0px" },
);

sections.forEach((s) => io.observe(s));

// ── Search ────────────────────────────────────────────────────────────────────
const searchInput = document.getElementById("searchInput");

// Ctrl+K focus shortcut
document.addEventListener("keydown", (e) => {
  if ((e.ctrlKey || e.metaKey) && e.key === "k") {
    e.preventDefault();
    searchInput?.focus();
  }
  if (e.key === "Escape" && document.activeElement === searchInput) {
    searchInput.blur();
    clearSearch();
  }
});

function clearSearch() {
  // Remove existing highlights
  document.querySelectorAll("mark").forEach((m) => {
    const parent = m.parentNode;
    parent.replaceChild(document.createTextNode(m.textContent), m);
    parent.normalize();
  });
}

searchInput?.addEventListener("input", function () {
  clearSearch();
  const q = this.value.trim();
  if (q.length < 2) return;

  const re = new RegExp(`(${q.replace(/[.*+?^${}()|[\]\\]/g, "\\$&")})`, "gi");

  // Walk text nodes in .main and highlight matches
  const walker = document.createTreeWalker(
    document.getElementById("mainContent"),
    NodeFilter.SHOW_TEXT,
    {
      acceptNode: (n) => {
        if (!n.nodeValue.trim()) return NodeFilter.FILTER_REJECT;
        if (["SCRIPT", "STYLE", "CODE", "PRE"].includes(n.parentNode.nodeName))
          return NodeFilter.FILTER_REJECT;
        return re.test(n.nodeValue)
          ? NodeFilter.FILTER_ACCEPT
          : NodeFilter.FILTER_REJECT;
      },
    },
  );

  const matches = [];
  let node;
  re.lastIndex = 0;
  while ((node = walker.nextNode())) matches.push(node);

  matches.forEach((n) => {
    re.lastIndex = 0;
    const frag = document.createDocumentFragment();
    let last = 0,
      m;
    while ((m = re.exec(n.nodeValue)) !== null) {
      frag.appendChild(
        document.createTextNode(n.nodeValue.slice(last, m.index)),
      );
      const mark = document.createElement("mark");
      mark.textContent = m[0];
      frag.appendChild(mark);
      last = re.lastIndex;
    }
    frag.appendChild(document.createTextNode(n.nodeValue.slice(last)));
    n.parentNode.replaceChild(frag, n);
  });

  // Scroll to first match
  const first = document.querySelector("mark");
  if (first) first.scrollIntoView({ behavior: "smooth", block: "center" });
});

// ── Code copy buttons ─────────────────────────────────────────────────────────
document.querySelectorAll(".code-copy").forEach((btn) => {
  btn.addEventListener("click", async () => {
    const code = btn.closest(".code-block").querySelector("pre");
    try {
      await navigator.clipboard.writeText(code.innerText);
      btn.textContent = "Copied!";
      btn.style.color = "#4ade80";
      setTimeout(() => {
        btn.textContent = "Copy";
        btn.style.color = "";
      }, 1500);
    } catch {
      btn.textContent = "Failed";
      setTimeout(() => {
        btn.textContent = "Copy";
      }, 1500);
    }
  });
});

// Inject copy buttons into all code blocks that don't have them
document.querySelectorAll(".code-block").forEach((block) => {
  const header = block.querySelector(".code-header");
  if (header && !header.querySelector(".code-copy")) {
    const btn = document.createElement("button");
    btn.className = "code-copy";
    btn.textContent = "Copy";
    btn.addEventListener("click", async () => {
      const pre = block.querySelector("pre");
      try {
        await navigator.clipboard.writeText(pre.innerText);
        btn.textContent = "Copied!";
        btn.style.color = "#4ade80";
        setTimeout(() => {
          btn.textContent = "Copy";
          btn.style.color = "";
        }, 1500);
      } catch {
        btn.textContent = "Failed";
        setTimeout(() => (btn.textContent = "Copy"), 1500);
      }
    });
    header.appendChild(btn);
  }
});
